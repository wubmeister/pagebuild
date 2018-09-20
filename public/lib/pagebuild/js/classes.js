var AJAX = {
    request: function(options) {
        var xhr = new XMLHttpRequest();

        xhr.addEventListener('readystatechange', function(){
            if (this.readyState == 4) {
                if (this.status == 200) {
                    var result = this.response;
                    if (typeof result == 'string') {
                        result = JSON.parse(result);
                    }
                    if (!result) {
                        if ('error' in options) {
                            options.error('Empty or invalid response');
                        }
                    } else if (!result.success) {
                        if ('error' in options) {
                            options.error(result.error || 'Request failed');
                        }
                    } else {
                        if ('success' in options) {
                            options.success(result.data||{});
                        }
                    }
                } else {
                    if ('error' in options) {
                        options.error('Response status ' + this.status + ' ' + this.statusText);
                    }
                }
            }
        });

        xhr.open(options.mehtod||'get', options.url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(options.data||null);
    }
};

var Template = {
    interpolate: function(template, variables) {
        var html = template,
            matches = template.match(/\{\{[^\}]+\}\}/g),
            key;

        if (!matches) return html;

        for (let i = 0; i < matches.length; i++) {
            key = matches[i].substr(2, matches[i].length-4);
            if (key in variables) {
                html = html.replace(matches[i], variables[key]);
            }
        }

        return html;
    }
};

var Interface = {
    TOP: 1,
    RIGHT: 2,
    BOTTOM: 4,
    LEFT: 8,
    CENTER: 16,
    MIDDLE: 32,
    currActivator: null,
    selectedNode: null,
    getDimensions: function(box) {
        var dim = { top: 0, left: 0, width: box.offsetWidth, height: box.offsetHeight };

        while (box) {
            dim.top += box.offsetTop;
            dim.left += box.offsetLeft;
            box = box.offsetParent;
        }

        return dim;
    },
    placeElement: function(element, reference, placement, offset) {
        var width = element.offsetWidth;
        var height = element.offsetHeight;
        var vpWidth = document.body.clientWidth;
        var left, top;

        offset = offset || 10;

        switch (placement) {
            case 'n':
                left = reference.left + (reference.width - width) / 2;
                top = reference.top - height - offset;
                break;
            case 'nne':
                left = reference.left;
                top = reference.top - height - offset;
                break;
            case 'ne':
                left = reference.left + reference.width + offset;
                top = reference.top - height - offset;
                break;
            case 'ene':
                left = reference.left + reference.width + offset;
                top = reference.top;
                break;
            case 'e':
                left = reference.left + reference.width + offset;
                top = reference.top + (reference.height - height) / 2;
                break;
            case 'ese':
                left = reference.left + reference.width + offset;
                top = reference.top + reference.height - height;
                break;
            case 'se':
                left = reference.left + reference.width + offset;
                top = reference.top + reference.height + offset;
                break;
            case 'sse':
                left = reference.left;
                top = reference.top + reference.height + offset;
                break;
            case 's':
                left = reference.left + (reference.width - width) / 2;
                top = reference.top + reference.height + offset;
                break;
        }

        left = Math.max(0, Math.min(vpWidth - width, left));
        top = Math.max(0, top);

        element.style.left = left + 'px';
        element.style.top = top + 'px';
    },
    markElementSuccess: function(pbBox) {
        var dim = this.getDimensions(pbBox);
        if (!this.successBox) {
            this.successBox = document.createElement('div');
            this.successBox.className = '_pb_success_box';
            document.body.appendChild(this.successBox);
        }

        this.successBox.style.top = dim.top + 'px';
        this.successBox.style.left = dim.left + 'px';
        this.successBox.style.width = dim.width + 'px';
        this.successBox.style.height = dim.height + 'px';
        this.successBox.classList.add('_pb_visible');
    },
    markElementError: function(pbBox, message) {
        var dim = this.getDimensions(pbBox);
        if (!this.errorBox) {
            this.errorBox = document.createElement('div');
            this.errorBox.className = '_pb_success_box';
            document.body.appendChild(this.errorBox);
        }

        this.errorBox.innerHTML = message ? '<span>' + message + '</span>' : '';

        this.errorBox.style.top = dim.top + 'px';
        this.errorBox.style.left = dim.left + 'px';
        this.errorBox.style.width = dim.width + 'px';
        this.errorBox.style.height = dim.height + 'px';
        this.errorBox.classList.add('_pb_visible');
    },
    showAddBox: function(activator, components) {
        this.hideSettingsBox();

        var content = this.addBox.querySelector('._pb_content');

        var html = '<div class="_pb_grid">';
        for (let i = 0; i < components.length; i++) {
            html += '<div class="_pb_item"><a class="_pb_component_select" data-component="' + components[i].name + '"><img src="' + components[i].thumb + '" /></a></div>';
        }
        html += '</div>';
        content.innerHTML = html;

        var dim = this.getDimensions(activator);
        this.placeElement(this.addBox, dim, 's');
        this.addBox.classList.add('_pb_visible');
        this.currActivator = activator;
    },
    hideAddBox: function() {
        this.addBox.classList.remove('_pb_visible');
        this.currActivator = null;
    },
    updateBoundingBox: function(nodeElement) {
        var dim = this.getDimensions(nodeElement);
        this.boundingBox.style.left = dim.left + 'px';
        this.boundingBox.style.top = dim.top + 'px';
        this.boundingBox.style.width = dim.width + 'px';
        this.boundingBox.style.height = dim.height + 'px';
    },
    selectNode: function(nodeElement) {
        if (!nodeElement.wtNode) {
            console.error('Not a working tree node element');
            return;
        }

        this.hideAddBox();
        this.updateBoundingBox(nodeElement);
        this.selectedNode = nodeElement.wtNode;
        this.boundingBox.className = '_pb_bounding_box _pb_visible _pb_or_' + (this.selectedNode.parent ? this.selectedNode.parent.getContentOrientation() : 'vertical');

        setTimeout(function(){
            Interface.showSettingsBox(Interface.selectedNode);
        }, 100);
    },
    unselectNode: function() {
        this.selectedNode = null;
        this.boundingBox.classList.remove('_pb_visible');
        this.hideSettingsBox();
    },
    showSettingsBox: function(node) {
        if (node.component.settings.length == 0) {
            this.settingsBox.classList.remove('_pb_visible');
            return;
        }

        var settingsContent = this.settingsBox.querySelector('._pb_content'),
            selects;

        settingsContent.innerHTML = this.buildSettingsForm(node.component.settings, node.settings);
        this.placeElement(this.settingsBox, this.getDimensions(this.boundingBox), 's', 20);
        this.settingsBox.classList.add('_pb_visible');

        selects = settingsContent.querySelectorAll('._pb_select');
        for (let i = 0; i < selects.length; i++) {
            CustomSelect(selects[i]);
        }
    },
    hideSettingsBox: function() {
        this.settingsBox.classList.remove('_pb_visible');
    },
    updateSettingValue: function(name, value) {
        if (this.selectedNode) {
            this.selectedNode.settings[name] = value;
            this.selectedNode.render();
            Interface.updateBoundingBox(Interface.selectedNode.element);
        }
    },
    placeComponent: function (component) {
        var node = new WorkingTreeNode(component);
        var placement = this.currActivator.getAttribute('data-placement') || 'replace';
        var parent, box;

        if (placement == 'replace') {
            box = this.currActivator;
            while (box && !box.classList.contains('_pb_box')) {
                box = box.parentElement;
            }
            if (box && box.wtNode) {
                box.wtNode.appendChild(node);
            }
        } else {
            var refComponent = this.selectedNode;
            if (placement == 'before') {
                refComponent.parent.insertBefore(node, refComponent);
            } else {
                refComponent.parent.insertAfter(node, refComponent);
            }
        }

        this.selectNode(node.element);
    },
    removeNode: function(node) {
        var parent = node.element.parentElement;
        parent.removeChild(node.element);
        if (!parent.firstElementChild) {
            parent.innerHTML = '<a class="_pb_circle_button _pb_add_button"><i class="material-icons">add</i></a>';
        }
    },
    init: function() {
        this.addBox = document.createElement('div');
        this.addBox.className = '_pb_panel _pb_blue';
        this.addBox.innerHTML = '<div class="_pb_header">Add component</div><div class="_pb_content"></div>';
        this.addBox.style.width = '280px';
        this.addBox.addEventListener('click', function(e){
            e.preventDefault();
            e.stopPropagation();

            var el = e.target;
            while (el && !el.classList.contains('_pb_component_select')) {
                el = el.parentElement;
            }

            if (el) {
                var componentName = el.getAttribute('data-component');
                var component = Component.get(componentName);

                if (component) {
                    Interface.placeComponent(component);
                    Interface.hideAddBox();
                }
            }
        });
        document.body.appendChild(this.addBox);

        this.boundingBox = document.createElement('div');
        this.boundingBox.className = '_pb_bounding_box';
        this.boundingBox.innerHTML = `
            <div class="_pb_button_group _pb_top">
                <a class="_pb_action_button _pb_insert_before" data-action="add" data-placement="before"><i class="material-icons">add</i></a>
                <a class="_pb_action_button _pb_move_left"><i class="material-icons">keyboard_arrow_up</i></a>
            </div>
            <div class="_pb_button_group _pb_right">
                <a class="_pb_action_button _pb_insert_after" data-action="add" data-placement="after"><i class="material-icons">add</i></a>
                <a class="_pb_action_button _pb_move_right"><i class="material-icons">keyboard_arrow_right</i></a>
            </div>
            <div class="_pb_button_group _pb_bottom">
                <a class="_pb_action_button _pb_insert_after" data-action="add" data-placement="after"><i class="material-icons">add</i></a>
                <a class="_pb_action_button _pb_move_right"><i class="material-icons">keyboard_arrow_down</i></a>
            </div>
            <div class="_pb_button_group _pb_left">
                <a class="_pb_action_button _pb_insert_before" data-action="add" data-placement="before"><i class="material-icons">add</i></a>
                <a class="_pb_action_button _pb_move_left"><i class="material-icons">keyboard_arrow_left</i></a>
            </div>
            <a class="_pb_action_button _pb_circle_button _pb_delete" data-action="delete"><i class="material-icons">delete</i></a>`;
        this.boundingBox.addEventListener('click', function(e){
            e.preventDefault();
            e.stopPropagation();

            var el = e.target;
            while (el && !el.classList.contains('_pb_action_button')) {
                el = el.parentElement;
            }

            if (el) {
                switch (el.getAttribute('data-action')) {
                    case 'add':
                        Interface.showAddBox(el, Interface.selectedNode.parent.component.getAllowedChildren());
                        break;
                    case 'delete':
                        if (confirm("Are you sure you want to delete this component?")) {
                            Interface.removeNode(Interface.selectedNode);
                            Interface.unselectNode();
                        }
                        break;
                }
            }
        });
        document.body.appendChild(this.boundingBox);

        this.settingsBox = document.createElement('div');
        this.settingsBox.className = '_pb_panel';
        this.settingsBox.innerHTML = '<div class="_pb_header">Settings</div><div class="_pb_content"></div>';
        this.settingsBox.style.width = '280px';
        this.settingsBox.addEventListener('change', function(e) {
            var input = e.target,
                name = input.name,
                value = null;

            if (input.tagName.toLowerCase() == 'select') {
                if (input.selectedIndex > -1) {
                    value = input.options[input.selectedIndex].value;
                }
            } else if (input.type == 'checkbox') {
                value = input.checked;
            } else if (input.type == 'radio') {
                if (input.checked) {
                    value = input.value;
                }
            } else {
                value = input.value;
            }

            if (value !== null) {
                Interface.updateSettingValue(name, value);
            }
        });
        document.body.appendChild(this.settingsBox);

        window.addEventListener('resize', this.onResize.bind(this));
    },
    onResize: function() {
        if (this.selectedNode) {
            this.updateBoundingBox(this.selectedNode.element)
        }
    },
    buildSettingsForm: function(settings, values) {
        var formHtml = '', value;

        values = values || {};

        for (let i = 0; i < settings.length; i++) {
            value = (settings[i].name in values) ? values[settings[i].name] : (settings[i].defaultValue||'');
            formHtml += `<div class="_pb_field">
                <label for="setting-${settings[i].name}">${settings[i].label}</label>`;

            switch (settings[i].type) {
                case 'check':
                    formHtml += `<div class="_pb_switch">
                            <input type="checkbox" id="setting-${settings[i].name}" name="${settings[i].name}"${value ? ' checked' : ''} />
                            <label for="setting-${settings[i].name}"></label>
                        </div>`;
                    break;

                case 'select':
                    formHtml += `<div class="_pb_select">
                        <span class="_pb_label"></span>
                        <select id="setting-${settings[i].name}" name="${settings[i].name}">`;
                    for (let key in settings[i].options) {
                        formHtml += `<option value="${key}"${key == value ? ' selected' : ''}>${settings[i].options[key]}</option>`;
                    }
                    formHtml += `</select>
                        </div>`;
                    break;

                default:
                    formHtml += `<input type="text" placeholder="" id="setting-${settings[i].name}" name="${settings[i].name}" value="${value}" />`;
                    break;
            }

            formHtml += `</div>`;
        }

        return formHtml;
    }
};

class WorkingTreeNode {
    constructor(component) {
        this.element = document.createElement('div');
        this.element.className = '_pb_box';
        this.contentElement = null;

        if (component instanceof Component) {
            this.id = null;
            this.component = component;
            this.settings = component.getDefaultSettings();
            this.children = [];
            this.render();
        } else {
            this.id = component.id || null;
            this.component = Component.get(component.component);
            this.settings = component.settings || {};
            this.render();
            if ('children' in component) {
                for (let i = 0; i < component.children.length; i++) {
                    this.appendChild(new WorkingTreeNode(component.children[i]));
                }
            }
        }
    }

    appendChild(child) {
        if (this.children.length == 0) {
            this.contentElement.innerHTML = '';
        }
        child.parent = this;
        this.children.push(child);
        this.contentElement.appendChild(child.element);
    }

    insertBefore(child, reference) {
        if (reference.parent != this) {
            console.error('Reference node is not a direct child');
            return;
        }
        child.parent = this;
        this.contentElement.insertBefore(child.element, reference.element);
    }

    insertAfter(child, reference) {
        if (reference.parent != this) {
            console.error('Reference node is not a direct child');
            return;
        }
        child.parent = this;
        this.contentElement.insertBefore(child.element, reference.element.nextSibling);
    }

    render() {
        var result = this.component.updateElement(this.element, this.settings, this.contentElement);
        this.element = result.element;
        this.contentElement = result.contentElement;
        this.element.wtNode = this;
    }

    pushChanges(apiPath) {
        var url = apiPath,
            self = this,
            method = 'post';

        if (this.id) {
            url += '/' + this.id;
            if (this.isDeleted) {
                method = 'delete';
            }
        }

        AJAX.request({
            method: method,
            url: url,
            success: function(result) {
                self.id = result.id;
                if (method == 'delete' && result.id == self.id) {
                    self.purge();
                } else {
                    Interface.markElementSuccess(this.element);
                }
            },
            error: function(errorMessage) {
                Interface.markElementError(this.element, errorMessage);
                alert(errorMessage);
            }
        });
    }

    getContentOrientation() {
        if (!this.contentElement.pbOrientation) {
            var style = getComputedStyle(this.contentElement);
console.log(style.display, style.flexDirection);
            if ((style.display == 'flex' || style.display == 'inline-flex') && (style.flexDirection == 'row' || style.flexDirection == 'row-reverse')) {
                this.contentElement.pbOrientation = 'horizontal';
            } else {
                this.contentElement.pbOrientation = 'vertical';
            }
        }

        return this.contentElement.pbOrientation;
    }
}

class Component {
    constructor(name, specs) {
        this.name = name;
        this.label = specs.label || name;
        this.thumb = specs.thumb || '';
        this.html = specs.html;
        this.allowChildren = specs.allowChildren || [];
        this.allowParents = specs.allowParents || [];
        this.settings = specs.settings || [];
        this.allowedChildren = null;
    }

    updateElement(element, settings, contentElement) {
        var tempDiv = document.createElement('div'),
            swapElement;

        var html = Template.interpolate(this.html, settings)
            .replace('{{content_class}}', '_pb_content')
            .replace('{{content}}', '<div class="_pb_content"></div>');
        tempDiv.innerHTML = html;

        if (!tempDiv.firstElementChild) {
            console.error('Could not render component');
        } else {
            tempDiv.firstElementChild.classList.add('_pb_box');
            if (element.parentElement) {
                swapElement = tempDiv.firstElementChild;
                element.parentElement.replaceChild(swapElement, element);
                element = swapElement;
            } else {
                element = tempDiv.firstElementChild;
            }

            if (contentElement) {
                var oldContent = element.querySelector('._pb_content');
                if (!oldContent && element.classList.contains('_pb_content')) oldContent = element;
                if (oldContent) {
                    if (oldContent == element) {
                        element = contentElement;
                    }
                    contentElement.className = oldContent.className;
                    oldContent.parentElement.replaceChild(contentElement, oldContent);
                }
            } else {
                contentElement = element.querySelector('._pb_content');
                if (!contentElement && element.classList.contains('_pb_content')) contentElement = element;

                if (contentElement) {
                    contentElement.innerHTML = '<a class="_pb_circle_button _pb_add_button"><i class="material-icons">add</i></a>';
                }
            }
        }

        return {
            contentElement: contentElement,
            element: element
        };
    }

    getDefaultSettings() {
        var settings = {};

        for (let i = 0; i < this.settings.length; i++) {
            settings[this.settings[i].name] = ('defaultValue' in this.settings[i]) ? this.settings[i].defaultValue : null;
        }

        return settings;
    }

    getAllowedChildren() {
        if (!this.allowedChildren) {
            this.allowedChildren = [];

            if (this.allowChildren.length > 0) {
                for (let i = 0; i < this.allowChildren.length; i++) {
                    this.allowedChildren.push(Component.get(this.allowChildren[i]));
                }
            } else {
                for (let key in Component.instances) {
                    let instance = Component.instances[key];
                    if (instance != this) {
                        if (instance.allowParents.length == 0 || instance.allowParents.indexOf(this.name) > -1) {
                            this.allowedChildren.push(instance);
                        }
                    }
                }
            }
        }

        return this.allowedChildren;
    }
}
Component.instances = [];
Component.get = function(name) {
    return Component.instances[name]||null;
};
Component.load = function(components) {
    for (let key in components) {
        this.instances[key] = new Component(key, components[key]);
    }
};
Component.all = function() {
    var all = [];
    for (let key in this.instances) {
        all.push(this.instances[key]);
    }

    return all;
}
