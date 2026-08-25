(function () {
    'use strict';

    var form = document.querySelector('[data-role-form]');
    if (!form) {
        return;
    }

    var employmentType = form.querySelector('[data-employment-type]');
    var tempHint = form.querySelector('[data-temp-hint]');
    var remoteToggle = form.querySelector('[data-remote-toggle]');
    var locationFields = form.querySelector('[data-location-fields]');

    function updateTempHint() {
        var isTemp = employmentType.value === 'temp' || employmentType.value === 'temp_to_hire';
        tempHint.style.display = isTemp ? 'block' : 'none';
    }

    function updateLocationFields() {
        var isRemote = remoteToggle.checked;
        locationFields.style.opacity = isRemote ? '0.5' : '1';
        locationFields.querySelectorAll('input').forEach(function (input) {
            input.disabled = isRemote;
        });
    }

    employmentType.addEventListener('change', updateTempHint);
    remoteToggle.addEventListener('change', updateLocationFields);

    updateTempHint();
    updateLocationFields();
})();
