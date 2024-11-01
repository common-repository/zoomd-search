jQuery(document).ready(function($) {
    var injector = document.createElement("div");
    var src = '<button type="button" id="search-btn" class="search-btn" zoomdsearch="{\'trigger\' : \'OnClick\' }">' +
        '<span id="search-icon" class="material-icons">search</span></button>';
    injector.innerHTML = src;
    (document.getElementsByTagName("body")[0] || document.documentElement).appendChild(injector);
});