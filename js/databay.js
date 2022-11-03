window.databayOneDrivePlugin = (function(){
    const showStatus = function(info){
        const add = document.createElement('div');
        add.textContent = info.text;
        add.style.marginTop = '10px';
        return add;
    };

    const replacementPermaLink = function(info){
        if (document.querySelector('#current_perma_link')) {
            return;
        }
        const input = document.createElement('input');
        input.id = 'current_perma_link';
        input.style.display = 'none';
        input.value = info.permaLink;
        document.body.appendChild(input);
    };

    const first = '.tabDesktop';
    const second = '.media-heading';
    const add = function(info){
        document.addEventListener('DOMContentLoaded', replacementPermaLink.bind(null, info));

        const firstNode = document.querySelector(first);
        const status = showStatus(info);
        if(firstNode) {
            firstNode.insertBefore(firstNode.children[0], status);
        } else {
            [].slice.call(document.querySelectorAll(second)).forEach(function(x){
                x.parentNode.appendChild(status);
            });
        }
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
