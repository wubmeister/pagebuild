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
            key = matches[i].substr(2, matches[i].length-1);
            if (key in variables) {
                html = html.replace(matches[i], variables[key]);
            }
        }

        return html;
    }
};

var Interface = {
    getDimensions: function(box) {
        var dim = { top: 0; left: 0; width: box.offsetWidth, height: box.offsetHeight };

        while (box) {
            dim.top += box.offsetTop;
            dim.left += box.offsetLeft;
            box = box.offsetParent;
        }

        return dim;
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
        result = this.component.updateElement(this.element, this.settings, this.contentElement);
        this.contentElement = result.contentElement;
    }

    pushChanges(apiPath) {
        var url = apiPath,
            self = this,
            method: 'post';

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
                if (method == 'delete' && result.id = self.id) {
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
    updateElement(element, settings, contentElement) {
        var tempDiv = document.createElement('div');

        tempDiv.innerHTML = Template.interpolate(this.html, settings)
            .replace('{{content_class}}', '._pb_content')
            .replace('{{content}}', '<div class="_pb_content"></div>');

        if (!tempDiv.firstElementChild) {
            console.error('Could not render component');
        } else {
            tempDiv.firstElementChild.classList.add('_pb_box');
            element.parent.replaceChild(tempDiv.firstElementChild, element);
            element = tempDiv.firstElementChild;
            if (contentElement) {
                var oldContent = element.querySelector('._pb_content');
                contentElement.className = oldContent.className;
                oldContent.parentElement.replaceChild(contentElement, oldContent);
            } else {
                contentElement = element.querySelector('._pb_content');
            }
        }

        return {
            contentElement: contentElement
        };
    }

    getDefaultSettings() {
        var settings = {};

        for (let key in this.settings) {
            settings[key] = this.settings[key].defaultValue || null;
        }

        return settings;
    }
}
Component.get = function(name) {
    return Component.instances[name]||null;
};
