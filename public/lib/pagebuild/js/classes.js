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

        offset = offset || 10;

        switch (placement) {
            case 'n':
                element.style.left = (reference.left + (reference.width - width) / 2) + 'px';
                element.style.top = (reference.top - height - offset) + 'px';
                break;
            case 'nne':
                element.style.left = reference.left + 'px';
                element.style.top = (reference.top - height - offset) + 'px';
                break;
            case 'ne':
                element.style.left = (reference.left + reference.width + offset) + 'px';
                element.style.top = (reference.top - height - offset) + 'px';
                break;
            case 'ene':
                element.style.left = (reference.left + reference.width + offset) + 'px';
                element.style.top = reference.top + 'px';
                break;
            case 'e':
                element.style.left = (reference.left + reference.width + offset) + 'px';
                element.style.top = (reference.top + (reference.height - height) / 2) + 'px';
                break;
            case 'ese':
                element.style.left = (reference.left + reference.width + offset) + 'px';
                element.style.top = (reference.top + reference.height - height) + 'px';
                break;
            case 'se':
                element.style.left = (reference.left + reference.width + offset) + 'px';
                element.style.top = (reference.top + reference.height + offset) + 'px';
                break;
            case 'sse':
                element.style.left = reference.left + 'px';
                element.style.top = (reference.top + reference.height + offset) + 'px';
                break;
            case 's':
                element.style.left = (reference.left + (reference.width - width) / 2) + 'px';
                element.style.top = (reference.top + reference.height + offset) + 'px';
                break;

        }
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
    placeComponent: function (component) {
        var node = new WorkingTreeNode(component);
        this.currActivator.parentElement.insertBefore(node.element, this.currActivator);
        this.currActivator.parentElement.removeChild(this.currActivator);
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
        this.children.push(child);
        this.contentElement.appendChild(child.element);
    }

    render() {
        var result = this.component.updateElement(this.element, this.settings, this.contentElement);
        this.element = result.element;
        this.contentElement = result.contentElement;
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
}

class Component {
    constructor(name, specs) {
        this.name = name;
        this.label = specs.label || name;
        this.thumb = specs.thumb || '';
        this.html = specs.html;
        this.allowChildren = specs.allowChildren || [];
        this.allowParents = specs.allowParents || [];
        this.settings = specs.settings || {};
    }

    updateElement(element, settings, contentElement) {
        var tempDiv = document.createElement('div');

        var html = Template.interpolate(this.html, settings)
            .replace('{{content_class}}', '_pb_content')
            .replace('{{content}}', '<div class="_pb_content"></div>');
        tempDiv.innerHTML = html;

        if (!tempDiv.firstElementChild) {
            console.error('Could not render component');
        } else {
            tempDiv.firstElementChild.classList.add('_pb_box');
            if (element.parentElement) {
                element.parentElement.replaceChild(tempDiv.firstElementChild, element);
            }
            element = tempDiv.firstElementChild;
            if (contentElement) {
                var oldContent = element.querySelector('._pb_content');
                if (!oldContent && element.classList.contains('_pb_content')) oldContent = element;
                if (contentElement) {
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
        var children = [];

        if (this.allowChildren.length > 0) {
            for (let i = 0; i < this.allowChildren.length; i++) {
                children.push(Component.get(this.allowChildren[i]));
            }
        } else {
            for (let key in Component.instances) {
                let instance = Component.instances[key];
                if (instance != this) {
                    if (instance.allowParents.length || instance.allowParents.indexOf(this.name) > -1) {
                        children.push(instance);
                    }
                }
            }
        }

        return children;
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
