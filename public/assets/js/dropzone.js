(function () {
    'use strict';

    document.querySelectorAll('[data-dropzone]').forEach(function (zone) {
        var input = zone.querySelector('input[type="file"]');
        var filenameEl = zone.querySelector('[data-dropzone-filename]');

        function showFile(file) {
            if (file) {
                filenameEl.textContent = 'Selected: ' + file.name + ' (' + Math.round(file.size / 1024) + ' KB)';
            }
        }

        input.addEventListener('change', function () {
            if (input.files && input.files[0]) {
                showFile(input.files[0]);
            }
        });

        ['dragenter', 'dragover'].forEach(function (evt) {
            zone.addEventListener(evt, function (e) {
                e.preventDefault();
                e.stopPropagation();
                zone.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach(function (evt) {
            zone.addEventListener(evt, function (e) {
                e.preventDefault();
                e.stopPropagation();
                zone.classList.remove('is-dragover');
            });
        });

        zone.addEventListener('drop', function (e) {
            var files = e.dataTransfer && e.dataTransfer.files;
            if (files && files[0]) {
                input.files = files;
                showFile(files[0]);
            }
        });
    });
})();
