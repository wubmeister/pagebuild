function RichTextEditor() {
    var rte,
        toolbar = document.createElement('div'),
        selection,
        hideTimeout = null,
        cancelHide = false,
        linkDialog = document.createElement('div'),
        linkHighlight = document.createElement('div');

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
            linkDialog.classList.remove('_pb_visible');
            linkHighlight.classList.remove('_pb_visible');
            rte = null;
            cancelHide = false;
        }, 200);
    }

    var toolbarItems = {
        style: {
            html: '<select data-action="style"><option value="p">Paragraph</option><option value="h1">Heading 1</option><option value="h2">Heading 2</option><option value="h3">Heading 3</option><option value="h4">Heading 4</option><option value="h5">Heading 5</option><option value="h6">Heading 6</option>',
            directAction: false,
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
            updateState: function(node, style, selection) {}
        },
        divider0: {
            html: '<span class="_pb_divider"></span>',
            updateState: function(node, style, selection) {}
        },
        bold: {
            html: '<a class="_pb_button" data-action="bold"><i class="material-icons">format_bold</i></a>',
            directAction: true,
            runCommand: function() {
                document.execCommand('bold');
            },
            updateState: function(node, style, selection) {
                if (style.fontWeight == 'bold') this.element.classList.add('active');
                else this.element.classList.remove('active');
            }
        },
        italic: {
            html: '<a class="_pb_button" data-action="italic"><i class="material-icons">format_italic</i></a>',
            directAction: true,
            runCommand: function() {
                document.execCommand('italic');
            },
            updateState: function(node, style, selection) {
                if (style.fontStyle == 'italic') this.element.classList.add('active');
                else this.element.classList.remove('active');
            }
        },
        underline: {
            html: '<a class="_pb_button" data-action="underline"><i class="material-icons">format_underline</i></a>',
            directAction: true,
            runCommand: function() {
                document.execCommand('underline');
            },
            updateState: function(node, style, selection) {
                if (style.textDecoration == 'underline') this.element.classList.add('active');
                else this.element.classList.remove('active');
            }
        },
        divider1: {
            html: '<span class="_pb_divider"></span>',
            updateState: function(node, style, selection) {}
        },
        left: {
            html: '<a class="_pb_button" data-action="left"><i class="material-icons">format_align_left</i></a>',
            directAction: true,
            runCommand: function() {
                document.execCommand('justifyLeft');
            },
            updateState: function(node, style, selection) {
                if (style.textAlign == 'left') this.element.classList.add('active');
                else this.element.classList.remove('active');
            }
        },
        center: {
            html: '<a class="_pb_button" data-action="center"><i class="material-icons">format_align_center</i></a>',
            directAction: true,
            runCommand: function() {
                document.execCommand('justifyCenter');
            },
            updateState: function(node, style, selection) {
                if (style.textAlign == 'center') this.element.classList.add('active');
                else this.element.classList.remove('active');
            }
        },
        right: {
            html: '<a class="_pb_button" data-action="right"><i class="material-icons">format_align_right</i></a>',
            directAction: true,
            runCommand: function() {
                document.execCommand('justifyRight');
            },
            updateState: function(node, style, selection) {
                if (style.textAlign == 'right') this.element.classList.add('active');
                else this.element.classList.remove('active');
            }
        },
        justify: {
            html: '<a class="_pb_button" data-action="justify"><i class="material-icons">format_align_justify</i></a>',
            directAction: true,
            runCommand: function() {
                document.execCommand('justifyFull');
            },
            updateState: function(node, style, selection) {
                if (style.textAlign == 'justify') this.element.classList.add('active');
                else this.element.classList.remove('active');
            }
        },
        divider2: {
            html: '<span class="_pb_divider"></span>',
            updateState: function(node, style, selection) {}
        },
        cut: {
            html: '<a class="_pb_button" data-action="cut"><i class="material-icons">content_cut</i></a>',
            directAction: true,
            runCommand: function() {
                document.execCommand('cut');
            },
            updateState: function(node, style, selection) {}
        },
        copy: {
            html: '<a class="_pb_button" data-action="copy"><i class="material-icons">content_copy</i></a>',
            directAction: true,
            runCommand: function() {
                document.execCommand('copy');
            },
            updateState: function(node, style, selection) {}
        },
        paste: {
            html: '<a class="_pb_button" data-action="paste"><i class="material-icons">content_paste</i></a>',
            directAction: true,
            runCommand: function() {
                if (navigator.clipboard) {
                    navigator.clipboard.readText().then(function(text) {
                        document.execCommand('insertText', false, text);
                    });
                } else {
                    document.execCommand('paste');
                }
            },
            updateState: function(node, style, selection) {}
        },
        undo: {
            html: '<a class="_pb_button" data-action="undo"><i class="material-icons">undo</i></a>',
            directAction: true,
            runCommand: function() {
                document.execCommand('undo');
            },
            updateState: function(node, style, selection) {}
        },
        redo: {
            html: '<a class="_pb_button" data-action="redo"><i class="material-icons">redo</i></a>',
            directAction: true,
            runCommand: function() {
                document.execCommand('redo');
            },
            updateState: function(node, style, selection) {}
        },
        divider3: {
            html: '<span class="_pb_divider"></span>',
            updateState: function(node, style, selection) {}
        },
        link: {
            html: '<a class="_pb_button" data-action="link"><i class="material-icons">link</i></a>',
            directAction: false,
            runCommand: function() {
                var rects = selection.getClientRects();
                if (rects.length > 0) {
                    var el = selection.startContainer;
                    if (el.nodeType != Node.ELEMENT_NODE) el = el.parentElement;
                    var node = el;
                    if (node.tagName.toLowerCase() == 'a') {
                        linkDialog.querySelector('[name="href"]').value = node.getAttribute('href');
                        var i, select = linkDialog.querySelector('[name="target"]'), target = node.getAttribute('target')||'_self';
                        for (i = 0; i < select.options.length; i++) {
                            if (select.options[i].value == target) {
                                select.selectedIndex = i;
                                break;
                            }
                        }

                        var top = 0, left = 0;
                        while (el) {
                            top += el.offsetTop;
                            left += el.offsetLeft;
                            el = el.offsetParent;
                        }

                        linkHighlight.style.left = left + 'px';
                        linkHighlight.style.top = top + 'px';
                        linkHighlight.style.width = node.offsetWidth + 'px';
                        linkHighlight.style.height = node.offsetHeight + 'px';
                    } else {
                        linkDialog.querySelector('[name="href"]').value = '';
                        linkDialog.querySelector('[name="target"]').selectedIndex = 0;

                        linkHighlight.style.left = rects[0].left + 'px';
                        linkHighlight.style.top = rects[0].top + 'px';
                        linkHighlight.style.width = rects[0].width + 'px';
                        linkHighlight.style.height = rects[0].height + 'px';
                    }

                    linkDialog.style.left = rects[0].left + 'px';
                    linkDialog.style.top = (rects[0].bottom + 8) + 'px';

                    linkDialog.classList.add('_pb_visible');
                    linkHighlight.classList.add('_pb_visible');
                }
            },
            updateState: function(node, style, selection) {
                if (selection.collapsed && node.tagName != 'A') this.element.classList.add('_pb_disabled');
                else this.element.classList.remove('_pb_disabled');
                if (node.tagName == 'A') this.element.classList.add('active');
                else this.element.classList.remove('active');
                // if (!this.isCommandRunning) {
                //     linkDialog.classList.remove('_pb_visible');
                // }
            }
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

            if (toolbarItems[action].directAction) {
                rte.focus();
                restoreSelection(selection);
            }
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

    linkDialog.className = '_pb_panel _pb_blue';
    linkDialog.innerHTML = `
        <div class="_pb_header">Link</div>
        <div class="_pb_content">

            <div class="_pb_field">
                <label for="rte_link_href">URL</label>
                <input type="text" name="href" id="rte_link_href" placeholder="URL" />
            </div>
            <div class="_pb_field">
                <label for="rte_link_target">Select field</label>
                <div class="_pb_select">
                    <span class="_pb_label"></span>
                    <select name="target" id="rte_link_target">
                        <option value="_self">Zelfde venster/frame</option>
                        <option value="_blank">Nieuw venster/tabblad</option>
                        <option value="_parent">Bovenliggende venster/frame</option>
                        <option value="_parent">Bovenste venster/frame</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="_pb_actions">
            <button type="button" class="_pb_button" data-action="cancel">Cancel</button>
            <button type="button" class="_pb_button" data-action="apply">Apply</button>
        </div>`;
    linkDialog.style.width = '280px';
    CustomSelect(linkDialog.querySelector('._pb_select'));

    document.body.appendChild(linkDialog);

    linkHighlight.className = '_pb_highlighter';
    document.body.appendChild(linkHighlight);

    linkDialog.addEventListener('mousedown', function(e){
        // selection = saveSelection();
        cancelHide = true;
    });

    linkDialog.addEventListener('click', function(e){
        if (e.target.tagName.toLowerCase() == 'button') {
            if (e.target.getAttribute('data-action') == 'apply') {
                var a = selection.startContainer;
                if (a.nodeType != Node.ELEMENT_NODE) a = a.parentElement;
                if (a.tagName.toLowerCase() == 'a') {
                    a.setAttribute('href', linkDialog.querySelector('[name="href"]').value);
                    a.setAttribute('target', linkDialog.querySelector('[name="target"]').value);
                    rte.focus();
                } else if (!selection.collapsed) {
                    var html = '<a href="' + linkDialog.querySelector('[name="href"]').value + '" target="' + linkDialog.querySelector('[name="target"]').value + '">' + selection + '</a>';
                    restoreSelection(selection);
                    document.execCommand('insertHTML', false, html);
                }
            } else {
                restoreSelection(selection);
            }

            linkDialog.classList.remove('_pb_visible');
            linkHighlight.classList.remove('_pb_visible');
            cancelHide = false;
        }
    });

    document.addEventListener('selectionchange', function(){
        if (!rte) return;

        var range = saveSelection();
        if (!range) return;

        var startNode = range.startContainer,
            el;
        if (startNode.nodeType != Node.ELEMENT_NODE) {
            startNode = startNode.parentElement;
        }
        el = startNode;
        while (el && el != rte) {
            el = el.parentElement;
        }
        if (el && el == rte) {
            var style = getComputedStyle(startNode);

            for (let key in toolbarItems) {
                toolbarItems[key].updateState(startNode, style, range);
            }
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
