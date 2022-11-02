window.databayOneDrivePlugin = (function(){
    const showStatusAt = function(node){
        const add = document.createElement('div');
        add.textContent = this.text;
        node.parentNode.appendChild(add);
    };

    const selector = '.media-heading';
    const add = function(info){
        [].slice.call(document.querySelectorAll(selector)).forEach(showStatusAt.bind(info));
    };

    return {
        show: function(info){
            add(info);
        },
        fileUploaded: function(response){
            const dummy = document.createElement('div');
            dummy.innerHTML = response;
            [].slice.call(dummy.children).forEach(document.body.appendChild.bind(document.body));
            $('body > .modal:last-of-type').modal({show: true});
        },
    };
})();
