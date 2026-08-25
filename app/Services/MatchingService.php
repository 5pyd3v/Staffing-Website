<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Rule-based scoring between an employer staffing request and the candidate
 * pool. This is deliberately transparent (no ML/black box) so a recruiter
 * can see *why* a candidate scored the way they did — each factor is capped
 * independently and the breakdown is returned alongside the total.
 *
 * Score out of 100:
 *   - Must-have skill overlap   : 0-40
 *   - Nice-to-have skill overlap: 0-15
 *   - Location / remote fit     : 0-15
 *   - Employment type match     : 0-10
 *   - Availability              : 0-10
 *   - Budget vs salary overlap  : 0-10
 */
final class MatchingService
{
    private const STRONG_MATCH_THRESHOLD = 65;
    private const NEEDS_REVIEW_THRESHOLD = 35;

    /**
     * Scores every active candidate against a staffing request and returns
     * them ranked highest-first. Candidates already placed/inactive are
     * excluded from the pool — they're not available to match.
     */
    public static function rankCandidatesForRequest(array $request, int $limit = 100): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT c.*, u.email,
                    GROUP_CONCAT(DISTINCT s.name SEPARATOR '|') AS skill_names
             FROM candidates c
             INNER JOIN users u ON u.id = c.user_id
             LEFT JOIN candidate_skills cs ON cs.candidate_id = c.id
             LEFT JOIN skills s ON s.id = cs.skill_id
             WHERE c.deleted_at IS NULL AND c.status IN ('new', 'in_review', 'shortlisted')
             GROUP BY c.id
             ORDER BY c.created_at DESC
             LIMIT {$limit}"
        );
        $stmt->execute();
        $candidates = $stmt->fetchAll();

        $scored = [];
        foreach ($candidates as $candidate) {
            $result = self::score($request, $candidate);
            $scored[] = array_merge($candidate, $result);
        }

        usort($scored, static fn ($a, $b) => $b['score'] <=> $a['score']);

        return $scored;
    }

    public static function score(array $request, array $candidate): array
    {
        $breakdown = [];

        $mustHave = self::tokenize((string) ($request['must_have_skills'] ?? ''));
        $niceToHave = self::tokenize((string) ($request['nice_to_have_skills'] ?? ''));
        $candidateSkills = self::tokenize(str_replace('|', ', ', (string) ($candidate['skill_names'] ?? '')));

        $mustHaveScore = self::skillOverlapScore($mustHave, $candidateSkills, 40);
        $breakdown['must_have_skills'] = $mustHaveScore;

        $niceToHaveScore = self::skillOverlapScore($niceToHave, $candidateSkills, 15);
        $breakdown['nice_to_have_skills'] = $niceToHaveScore;

        $locationScore = self::locationScore($request, $candidate);
        $breakdown['location'] = $locationScore;

        $employmentScore = self::employmentTypeScore($request, $candidate);
        $breakdown['employment_type'] = $employmentScore;

        $availabilityScore = self::availabilityScore($candidate);
        $breakdown['availability'] = $availabilityScore;

        $budgetScore = self::budgetScore($request, $candidate);
        $breakdown['budget'] = $budgetScore;

        $total = $mustHaveScore + $niceToHaveScore + $locationScore + $employmentScore + $availabilityScore + $budgetScore;

        return [
            'score' => round($total, 1),
            'breakdown' => $breakdown,
            'match_type' => self::matchTypeFor($total),
        ];
    }

    public static function matchTypeFor(float $score): string
    {
        if ($score >= self::STRONG_MATCH_THRESHOLD) {
            return 'strong_match';
        }

        return $score >= self::NEEDS_REVIEW_THRESHOLD ? 'needs_review' : 'rejected';
    }

    /**
     * @return string[] lowercase, trimmed, de-duplicated keyword tokens
     */
    private static function tokenize(string $text): array
    {
        $parts = preg_split('/[,;\n]+/', $text) ?: [];
        $tokens = array_map(static fn ($p) => mb_strtolower(trim($p)), $parts);

        return array_values(array_filter($tokens, static fn ($t) => $t !== ''));
    }

    private static function skillOverlapScore(array $required, array $candidateSkills, int $max): int
    {
        if ($required === []) {
            // Nothing specified to match against — neutral, not a penalty.
            return (int) round($max * 0.5);
        }

        $matches = 0;
        foreach ($required as $need) {
            foreach ($candidateSkills as $has) {
                if (self::phrasesOverlap($need, $has)) {
                    $matches++;
                    break;
                }
            }
        }

        return (int) round(($matches / count($required)) * $max);
    }

    /**
     * Requirements are often free-text phrases ("forklift certification")
     * while candidate skills are short tags ("Forklift Operation") — a
     * whole-phrase substring check misses that obvious overlap, so this
     * falls back to shared significant words (4+ letters, to skip "and"/
     * "with"/etc.) once a direct substring match fails.
     */
    private static function phrasesOverlap(string $a, string $b): bool
    {
        if ($a === $b || str_contains($b, $a) || str_contains($a, $b)) {
            return true;
        }

        $wordsA = array_filter(preg_split('/[^a-z0-9]+/', $a) ?: [], static fn ($w) => strlen($w) >= 4);
        $wordsB = array_filter(preg_split('/[^a-z0-9]+/', $b) ?: [], static fn ($w) => strlen($w) >= 4);

        return array_intersect($wordsA, $wordsB) !== [];
    }

    private static function locationScore(array $request, array $candidate): int
    {
        $requestRemoteOk = !empty($request['is_remote_ok']);
        $candidateRemoteOk = !empty($candidate['is_remote_ok']);

        if ($requestRemoteOk && $candidateRemoteOk) {
            return 15;
        }

        $requestCity = mb_strtolower(trim((string) ($request['location_city'] ?? '')));
        $requestState = mb_strtolower(trim((string) ($request['location_state'] ?? '')));
        $candidateCity = mb_strtolower(trim((string) ($candidate['location_city'] ?? '')));
        $candidateState = mb_strtolower(trim((string) ($candidate['location_state'] ?? '')));

        if ($requestCity !== '' && $requestCity === $candidateCity) {
            return 15;
        }

        if ($requestState !== '' && $requestState === $candidateState) {
            return 8;
        }

        return $requestCity === '' && $requestState === '' ? 8 : 0;
    }

    private static function employmentTypeScore(array $request, array $candidate): int
    {
        $requestType = (string) ($request['employment_type'] ?? '');
        $candidateTypes = explode(',', (string) ($candidate['employment_types'] ?? ''));

        return in_array($requestType, $candidateTypes, true) ? 10 : 0;
    }

    private static function availabilityScore(array $candidate): int
    {
        return match ($candidate['availability'] ?? '') {
            'immediate' => 10,
            '2_weeks' => 7,
            '1_month' => 4,
            default => 0,
        };
    }

    private static function budgetScore(array $request, array $candidate): int
    {
        $budgetMin = $request['budget_min'] ?? null;
        $budgetMax = $request['budget_max'] ?? null;
        $salaryMin = $candidate['salary_expectation_min'] ?? null;
        $salaryMax = $candidate['salary_expectation_max'] ?? null;

        if ($budgetMin === null && $budgetMax === null) {
            return 5;
        }

        if ($salaryMin === null && $salaryMax === null) {
            return 5;
        }

        $requestMax = $budgetMax ?? $budgetMin;
        $requestMin = $budgetMin ?? $budgetMax;
        $candidateMin = $salaryMin ?? $salaryMax;
        $candidateMax = $salaryMax ?? $salaryMin;

        // Overlapping ranges (candidate's ask fits within, or straddles, the budget).
        if ((float) $candidateMin <= (float) $requestMax && (float) $candidateMax >= (float) $requestMin) {
            return 10;
        }

        return 0;
    }
}
