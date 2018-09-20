function RichTextEditor() {
    var rte,
        toolbar = document.createElement('div'),
        selection,
        hideTimeout = null,
        cancelHide = false;

    /**
     * Safari/Firefox/Chrome/IE 10-11/Edge: Range
     */
    function saveSelection() {
        if (window.getSelection) {
            var sel = window.getSelection();
            if (sel.getRangeAt && sel.rangeCount) {
                return sel.getRangeAt(0);
            }
        } else if (document.selection && document.selection.createRange) {
            return document.selection.createRange();
        }
        return null;
    }

    function restoreSelection(range) {
        if (range) {
            if (window.getSelection) {
                var sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(range);
            } else if (document.selection && range.select) {
                range.select();
            }
        }
    }

    function hideToolbar() {
        if (hideTimeout) {
            clearTimeout(hideTimeout);
        }
        hideTimeout = setTimeout(function(){
            toolbar.classList.remove('_pb_visible');
            rte = null;
            cancelHide = false;
        }, 200);
    }

    var toolbarItems = {
        style: {
            html: '<select data-action="style"><option value="p">Paragraph</option><option value="h1">Heading 1</option><option value="h2">Heading 2</option><option value="h3">Heading 3</option><option value="h4">Heading 4</option><option value="h5">Heading 5</option><option value="h6">Heading 6</option>',
            runCommand: function() {
                var value = this.element.selectedIndex > -1 ? this.element.options[this.element.selectedIndex].value : null;

                var node = selection.startContainer;
                while (node && node.nodeType != Node.ELEMENT_NODE) {
                    node = node.parentElement;
                }

                var newNode = document.createElement(value);

                if (node == rte) {
                    newNode.innerHTML = selection.startContainer.textContent;
                    selection.startContainer.parentElement.replaceChild(newNode, selection.startContainer);
                } else {
                    newNode.innerHTML = node.innerHTML;
                    node.parentElement.replaceChild(newNode, node);
                }
            },
            updateState: function(node, style) {}
        },
        divider0: {
            html: '<span class="_pb_divider"></span>',
            updateState: function(node, style) {}
        },
        bold: {
            html: '<a class="_pb_button" data-action="bold"><i class="material-icons">format_bold</i></a>',
            runCommand: function() {
                document.execCommand('bold');
            },
            updateState: function(node, style) {
                if (style.fontWeight == 'bold') this.element.classList.add('active');
                else this.element.classList.remove('active');
            }
        },
        italic: {
            html: '<a class="_pb_button" data-action="italic"><i class="material-icons">format_italic</i></a>',
            runCommand: function() {
                document.execCommand('italic');
            },
            updateState: function(node, style) {
                if (style.fontStyle == 'italic') this.element.classList.add('active');
                else this.element.classList.remove('active');
            }
        },
        underline: {
            html: '<a class="_pb_button" data-action="underline"><i class="material-icons">format_underline</i></a>',
            runCommand: function() {
                document.execCommand('underline');
            },
            updateState: function(node, style) {
                if (style.textDecoration == 'underline') this.element.classList.add('active');
                else this.element.classList.remove('active');
            }
        },
        divider1: {
            html: '<span class="_pb_divider"></span>',
            updateState: function(node, style) {}
        },
        left: {
            html: '<a class="_pb_button" data-action="left"><i class="material-icons">format_align_left</i></a>',
            runCommand: function() {
                document.execCommand('justifyLeft');
            },
            updateState: function(node, style) {
                if (style.textAlign == 'left') this.element.classList.add('active');
                else this.element.classList.remove('active');
            }
        },
        center: {
            html: '<a class="_pb_button" data-action="center"><i class="material-icons">format_align_center</i></a>',
            runCommand: function() {
                document.execCommand('justifyCenter');
            },
            updateState: function(node, style) {
                if (style.textAlign == 'center') this.element.classList.add('active');
                else this.element.classList.remove('active');
            }
        },
        right: {
            html: '<a class="_pb_button" data-action="right"><i class="material-icons">format_align_right</i></a>',
            runCommand: function() {
                document.execCommand('justifyRight');
            },
            updateState: function(node, style) {
                if (style.textAlign == 'right') this.element.classList.add('active');
                else this.element.classList.remove('active');
            }
        },
        justify: {
            html: '<a class="_pb_button" data-action="justify"><i class="material-icons">format_align_justify</i></a>',
            runCommand: function() {
                document.execCommand('justifyFull');
            },
            updateState: function(node, style) {
                if (style.textAlign == 'justify') this.element.classList.add('active');
                else this.element.classList.remove('active');
            }
        },
        divider2: {
            html: '<span class="_pb_divider"></span>',
            updateState: function(node, style) {}
        },
        cut: {
            html: '<a class="_pb_button" data-action="cut"><i class="material-icons">content_cut</i></a>',
            runCommand: function() {
                document.execCommand('cut');
            },
            updateState: function(node, style) {}
        },
        copy: {
            html: '<a class="_pb_button" data-action="copy"><i class="material-icons">content_copy</i></a>',
            runCommand: function() {
                document.execCommand('copy');
            },
            updateState: function(node, style) {}
        },
        paste: {
            html: '<a class="_pb_button" data-action="paste"><i class="material-icons">content_paste</i></a>',
            runCommand: function() {
                if (navigator.clipboard) {
                    navigator.clipboard.readText().then(function(text) {
                        document.execCommand('insertText', false, text);
                    });
                } else {
                    document.execCommand('paste');
                }
            },
            updateState: function(node, style) {}
        },
        undo: {
            html: '<a class="_pb_button" data-action="undo"><i class="material-icons">undo</i></a>',
            runCommand: function() {
                document.execCommand('undo');
            },
            updateState: function(node, style) {}
        },
        redo: {
            html: '<a class="_pb_button" data-action="redo"><i class="material-icons">redo</i></a>',
            runCommand: function() {
                document.execCommand('redo');
            },
            updateState: function(node, style) {}
        }
    };

    (function(){
        var div = document.createElement('div');

        toolbar.className = '_pb_toolbar';
        document.body.appendChild(toolbar);

        for (let key in toolbarItems) {
            div.innerHTML = toolbarItems[key].html;
            toolbarItems[key].element = div.firstElementChild;
            toolbar.appendChild(toolbarItems[key].element);
        }
    })();

    toolbar.addEventListener('mousedown', function(e){
        selection = saveSelection();
        cancelHide = true;
    });

    toolbar.addEventListener('click', function(e){
        var el = e.target;

        if (hideTimeout) {
            clearTimeout(hideTimeout);
            hideTimeout = null;
        }

        while (el && el != this && !el.hasAttribute('data-action')) {
            el = el.parentElement;
        }

        if (el && el != this && el.tagName.toLowerCase() != 'select') {
            var action = el.getAttribute('data-action');

            rte.focus();
            restoreSelection(selection);
            cancelHide = false;

            toolbarItems[action].runCommand();
        }
    });

    toolbar.addEventListener('change', function(e){
        var el = e.target;
        var action = el.getAttribute('data-action');

        rte.focus();
        restoreSelection(selection);
        cancelHide = false;

        toolbarItems[action].runCommand();
    });

    document.addEventListener('selectionchange', function(){
        if (!rte) return;

        var range = saveSelection();
        if (!range) return;

        var startNode = range.startContainer;
        if (startNode.nodeType != Node.ELEMENT_NODE) {
            startNode = startNode.parentElement;
        }
        var style = getComputedStyle(startNode);

        for (let key in toolbarItems) {
            toolbarItems[key].updateState(startNode, style);
        }
    });

    function onFocus() {
        rte = this;
        var left = 0, top = 0, el = this;
        while (el) {
            if (el.offsetParent) {
                left += el.offsetLeft;
                top += el.offsetTop;
            }
            el = el.parentElement;
        }

        toolbar.style.left = left + 'px';
        toolbar.style.top = (top - 35) + 'px';
        toolbar.classList.add('_pb_visible');
    }

    function onBlur() {
        if (!cancelHide) {
            hideToolbar();
        }
    }

    this.edit = function(element) {
        element.addEventListener('focus', onFocus);
        element.addEventListener('blur', onBlur);
    };
}
