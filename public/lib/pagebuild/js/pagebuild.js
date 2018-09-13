function CustomSelect(element, options) {
    var label = element.querySelector('._pb_label');
    var select = element.querySelector('select');

    function onChange() {
        label.innerHTML = select.selectedIndex > -1 ? select.options[select.selectedIndex].innerHTML : '';
    }

    select.addEventListener('change', onChange);
    onChange();
}
