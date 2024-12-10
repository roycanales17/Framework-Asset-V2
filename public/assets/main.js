class $$ {

    /**
     * Registers a module by identifying a DOM element and invoking a callback.
     *
     * @param {(this: module, container: JX) => void} identifier - The ID of the DOM element to target.
     * @param {string} callback - The callback function to execute with the targeted element.
     * @throws {Error} - Throws an error if no element with the given ID is found.
     */
    static module(identifier = '',callback) {
        const container = document.getElementById(identifier);

        if (!container) {
            throw new Error(`Element with ID "${identifier}" not found.`);
        }

        if (!(container instanceof HTMLElement)) {
            throw new Error(`Element with ID "${identifier}" is not an HTMLElement.`);
        }

        try {
            window.__module__ = container;
            callback.call(this, JX(document.querySelectorAll(`#${identifier}`)));
            delete window.__module__;
        } catch (error) {
            this.throw(container, error);
        }
    }

    /**
     * Submits a form via a POST request and handles various states of the request.
     *
     * @param {string} success - The callback or event to trigger upon successful form submission.
     * @param {string} failed - The callback or event to trigger if the form submission fails.
     * @param {string} loader - The loader element or event to trigger while the request is being processed.
     * @param {Event} e - The form submit event.
     */
    static form(success, failed, loader, e) {
        e.preventDefault();
        try {
            let identifier = '';
            const form = e.target;
            const formData = new FormData(form);
            const postData = new URLSearchParams();

            formData.forEach((value, key) => {
                if (key === '__token__') {
                    identifier = value;
                } else {
                    postData.append(key, value);
                }
            });

            if (loader)
                this.trigger(loader, true);

            const throwError = (response, msg) => {
                if (failed) {
                    this.trigger(failed, response);
                }
            };

            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-App-Component': identifier
                },
                body: postData.toString()
            }).then(response => {
                if (!response.ok) {
                    return response.text().then(errorText => {
                        throwError({
                            'status': response.status,
                            'response': errorText
                        })
                    });
                }

                return response.text().then(data => ({
                    status: response.status,
                    response: data
                }));
            }).then(data => {
                if (data) {
                    this.trigger(success, data);
                }
            }).catch(error => {
                throwError(error, 'Something went wrong.');
            }).finally(() => {
                if (loader) {
                    this.trigger(loader, false);
                }
            });
        } catch (e) {
            console.error(e);
        }
    }

    /**
     * @param {Object<string, Object>} payload - An associative array (object), where each value is an empty object `{}`.
     * @param {string} identifier - A unique identifier.
     */
    static ajax(payload, identifier = '') {
        try {
            if (payload == null || (typeof payload !== 'object' && !Array.isArray(payload))) {
                throw new Error('Payload is not a valid object or array.');
            }

            if (Object.keys(payload).length === 0 && (Array.isArray(payload) ? payload.length === 0 : true)) {
                throw new Error('Payload is empty.');
            }

            return fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-App-Component': identifier
                },
                body: JSON.stringify(payload)
            }).then(response => {
                if (!response.ok) {
                    return response.text().then(errorText => ({
                        'status': response.status,
                        'response': errorText
                    }));
                }

                return response.text().then(data => ({
                    status: response.status,
                    response: data
                }));
            });
        } catch (e) {
            console.error('Ajax error:', e);
        }
    }

    /**
     * Listens for a custom event and invokes the callback when the event is triggered.
     *
     * @param {string} func - The name of the event to listen for.
     * @param {function} callback - The function to be called when the event is triggered. The callback receives the event's detail data as an argument.
     */
    static listen(func, callback) {
        window.addEventListener(func, (event) => callback(event.detail))
    }

    /**
     * Triggers a custom event and passes data to the event listeners.
     *
     * @param {string} func - The name of the event to trigger.
     * @param {any} data - The data to be passed to the event listeners via the event's `detail` property.
     */
    static trigger(func, data) {
        window.dispatchEvent(new CustomEvent(func, {detail: data}))
    }

    /**
     * Handles and logs a detailed error when an error occurs in a specified module.
     *
     * This function fetches the HTML content of the module, extracts the relevant details
     * from the error object (such as the message, stack trace, and line number), and
     * outputs a formatted error message along with a snippet of the surrounding code for debugging.
     *
     * @param {HTMLElement} container - The HTML container element associated with the module where the error occurred.
     *                                   The container's `data-module` attribute is used to log the module name.
     * @param {Error} error - The error object that contains the error message, stack trace, and other error details.
     */

    static throw(container, error) {

        fetch('')
            .then(response => response.text())
            .then(htmlContent =>
            {
                const module = !container ? 'UNKNOWN' : container.getAttribute('data-module');
                const stackTrace = error.stack || 'No stack trace available';
                const formattedStackTrace = stackTrace.replace(/\n/g, '\n    -');

                const redirectUrl = (module) => {
                    return `${window.location.protocol}//${window.location.host}?__module__=${module}`;
                }

                const errorLine = (stackTrace) => {
                    const regex = /:(\d+):\d+/g;
                    let match;

                    if ((match = regex.exec(stackTrace)) !== null) {
                        return parseInt(match[1]);
                    }

                    return null;
                }

                const errorFile = (stackTrace) => {
                    const lines = stackTrace.split('\n');
                    const isJavaScriptFile = (url) => {
                        const regex = /\/[^\/]+\.js(:\d+:\d+)?$/;
                        return regex.test(url);
                    };
                    for (let line of lines) {
                        const regex = /at\s+(.*)\s\((http[s]?:\/\/[^\)]+)\)/;
                        const match = line.match(regex);
                        if (match && match[2]) {
                            const filePath = match[2];

                            if (isJavaScriptFile(filePath)) {
                                return filePath;
                            }
                            break;
                        }
                    }

                    return "index";
                };

                const displayErrorContext = (content, errorLine, contextRange = 10) => {
                    const lines = content.split('\n');
                    const start = Math.max(0, errorLine - contextRange - 1);
                    const end = Math.min(lines.length, errorLine + contextRange);
                    const redBackground = '\x1b[41m';
                    const resetColor = '\x1b[0m';
                    const maxLineNumberLength = String(end).length;

                    const snippet = lines.slice(start, end).map((line, index) => {
                        const lineNumber = start + index + 1;
                        const paddedLineNumber = String(lineNumber).padStart(maxLineNumberLength, ' ');
                        const lineIndicator = lineNumber === errorLine ? '\t>' : '\t ';
                        const lineBackground = lineNumber === errorLine ? redBackground : '';
                        const lineStyled = `${lineBackground}${line}${resetColor}`;

                        return `${lineIndicator} ${paddedLineNumber} | ${lineStyled}`;
                    }).join('\n');

                    return `...\n${snippet}\n    ...`;
                };

                const searchModule = (content, startingLine) => {
                    startingLine = parseInt(startingLine);
                    const lines = content.split("\n");
                    for (let i = startingLine - 1; i >= 0; i--) {
                        const line = (lines[i] ?? '').trim();
                        const match = line.match(/data-module=['"]([^'"]+)['"]/);
                        if (match) {
                            return match[1];
                        }
                    }
                    return null;
                }

                const file = errorFile(stackTrace);
                const line = errorLine(stackTrace);
                const possibleModule = searchModule(htmlContent, line);

                console.error(`
    ============================================================
    
    Module:\t\t${module} ${(module !== 'UNKNOWN') ? `\n\tFind me:\t${redirectUrl(module)}` : ''} ${(module === 'UNKNOWN' && possibleModule) ? `\n\tPossible: \t${possibleModule}\n\tTry me:\t\t${redirectUrl(possibleModule)}` : ''}
    
    ------------------------------------------------------------
    
    Message:\t${error.message.replace(/\n/g, ' ')}
    File:\t\t${file || 'index' }
    Line:\t\t${line || 'N/A'}
    
    ------------------------------------------------------------
    
    Stack Trace:
    ${formattedStackTrace} ${(file === 'index' || file === '') ? `\n
    ------------------------------------------------------------
    
    ${displayErrorContext(htmlContent, line)}` : ''}
    
    ===========================================================
    `);
            });
    }

    /**
     * Registers a global error handler to catch and process uncaught JavaScript errors.
     *
     * This function ensures that the global `window.onerror` handler is registered only once.
     * When an error occurs, it checks if a module is active (`window.__module__`),
     * and if so, calls the `$$.throw` function to handle the error within that module.
     * The error details are logged to the console, including the error message, source file,
     * and line/column numbers.
     *
     * @returns {object} The current context (`this`), allowing for method chaining.
     */
    static errorHandler() {
        if (!window.__errorHandler__) {
            window.onerror = function (message, source, lineno, colno, error) {
                $$.throw(window.__module__ ?? null, error);
                return true;
            };
            window.__errorHandler__ = true;
        }
        return this;
    }

    /**
     * Static method to select DOM elements based on a given CSS selector.
     * It checks if a module is available on the global window object and uses that module to query the DOM.
     * If no module is found, it defaults to querying the entire document.
     *
     * @param {string} selector - A valid CSS selector used to query DOM elements. This can be any selector string such as an element type, class, id, or attribute.
     *
     * @returns {Object} Returns an instance of the $$$(elements) utility, which provides various DOM manipulation and utility methods on the selected elements.
     */
    static elem(selector) {
        if (window.__module__) {
            return JX(window.__module__.querySelectorAll(selector));
        }
        return JX(document.querySelectorAll(selector));
    }

    /**
     * Dynamically imports a JavaScript file into the document if it is not already included.
     * Ensures the script is loaded only once and optionally executes a callback after loading.
     *
     * @param {string} src - The URL of the JavaScript file to be loaded.
     * @param {Function} [callback] - An optional function to execute after the script is successfully loaded.
     */
    static import(src, callback) {
        if (document.querySelector(`script[src="${src}"]`)) {
            if (callback) callback();
            return;
        }

        const script = document.createElement("script");
        script.src = src;
        script.type = "text/javascript";
        script.onload = function () {
            if (callback) callback();
        };
        script.onerror = function () {
            console.error(`Failed to load script "${src}".`);
        };

        document.head.appendChild(script);
    }

    /**
     * Parses a JSON string and returns the corresponding object.
     *
     * This method safely parses a JSON string. It ensures the input is a valid
     * JSON string and catches any parsing errors.
     *
     * @param {string} jsonString - The JSON string to parse.
     * @returns {object|null} - The parsed object if successful, or null if the string is invalid.
     * @throws {Error} - Throws an error if the input is not a string.
     */
    static json(jsonString) {
        if (typeof jsonString !== 'string') {
            throw new Error('Input must be a string');
        }

        try {
            return JSON.parse(jsonString);
        } catch (error) {
            console.error('Failed to parse JSON:', error.message);
            return null;
        }
    }
}

/**
 * Represents a utility for DOM manipulation.
 * @class JX
 * @property {() => JX} log - Logs the elements to the console and returns the JX object.
 * @property {(className: string) => JX} addClass - Adds a class to each element in the collection and returns the JX object.
 * @property {(className: string) => JX} removeClass - Removes a class from each element in the collection and returns the JX object.
 * @property {(className: string) => JX} toggleClass - Toggles a class on each element in the collection and returns the JX object.
 * @property {(event: string, callback: (event: Event) => void) => JX} on - Attaches event listeners to each element and returns the JX object.
 * @property {(event: string) => JX} off - Removes event listeners from each element and returns the JX object.
 * @property {(event: string, data?: any) => JX} trigger - Triggers a custom event on each element in the collection and returns the JX object.
 * @property {(property: string, value?: string) => string|JX} css - Sets or retrieves CSS properties for elements. Returns the property value if no value is provided.
 * @property {(htmlContent?: string) => string|JX} html - Sets or retrieves the inner HTML of elements. Returns the HTML if no content is provided.
 * @property {(textContent?: string) => string|JX} text - Sets or retrieves the text content of elements. Returns the text if no content is provided.
 * @property {(value?: string) => string|JX} val - Sets or retrieves the value of form elements. Returns the value if no value is provided.
 * @property {(content: string|Element|JX) => JX} append - Appends content to each element in the collection and returns the JX object.
 * @property {(content: string|Element|JX) => JX} prepend - Prepends content to each element in the collection and returns the JX object.
 * @property {(content: string|Element|JX) => JX} before - Inserts content before each element in the collection and returns the JX object.
 * @property {(content: string|Element|JX) => JX} after - Inserts content after each element in the collection and returns the JX object.
 * @property {(selector: string) => JX} find - Finds descendants of elements that match a selector and returns a new JX object.
 * @property {(callback: (element: Element, index: number) => void) => JX} each - Iterates over each element in the collection and executes a callback.
 * @property {() => JX} hide - Hides each element by setting `display: none` and returns the JX object.
 * @property {() => JX} show - Shows each element by resetting the `display` property and returns the JX object.
 * @property {() => JX} remove - Removes each element from the DOM and returns the JX object.
 * @property {(attribute: string, value?: string) => string|JX} attr - Sets or retrieves attributes for elements. Returns the attribute value if no value is provided.
 * @property {(property: string, value?: any) => any|JX} prop - Sets or retrieves properties for elements. Returns the property value if no value is provided.
 * @property {(key: string, value?: any) => any|JX} data - Sets or retrieves data attributes for elements. Returns the data value if no value is provided.
 * @property {(className: string) => boolean} hasClass - Checks if any element in the collection has the specified class.
 * @property {(selector: string) => boolean} is - Checks if any element matches a selector.
 * @property {(selector: string) => JX} not - Filters out elements that match a selector and returns a new JX object.
 * @property {() => number|string|JX} width - Gets or sets the width of elements.
 * @property {() => number|string|JX} height - Gets or sets the height of elements.
 * @property {() => number} outerWidth - Retrieves the outer width of elements (includes borders).
 * @property {() => number} outerHeight - Retrieves the outer height of elements (includes borders).
 * @property {() => { top: number, left: number }} offset - Retrieves the top and left offsets of the first element.
 * @property {(selector: string|((element: Element) => boolean)) => JX} filter - Filters elements based on a selector or a callback.
 * @property {() => JX} first - Gets the first element in the collection and returns a new JX object.
 * @property {() => JX} last - Gets the last element in the collection and returns a new JX object.
 * @property {(index: number) => JX} eq - Gets the element at the specified index in the collection and returns a new JX object.
 * @property {(callback: (element: Element, index: number) => any) => any[]} map - Maps the collection elements to a new array based on a callback.
 * @property {() => JX} empty - Removes all child elements from each element in the collection and returns the JX object.
 * @property {(mouseenter: Function, mouseleave: Function) => JX} hover - Attaches hover event listeners for mouseenter and mouseleave.
 * @property {(callback: (event: Event) => void) => JX} click - Attaches a click event listener or triggers a click event.
 * @property {() => JX} focus - Sets focus on the first element in the collection.
 * @property {() => JX} blur - Removes focus from the first element in the collection.
 */
const JX = (elements) => {
    return {
        elements,

        log() {
            console.log(this.elements);
            return this;
        },

        addClass(className) {
            this.elements.forEach(element => {
                element.classList.add(className);
            });
            return this;
        },

        removeClass(className) {
            this.elements.forEach(element => {
                element.classList.remove(className);
            });
            return this;
        },

        toggleClass(className) {
            this.elements.forEach(element => {
                element.classList.toggle(className);
            });
            return this;
        },

        each(callback) {
            this.elements.forEach(callback);
            return this;  // Allow chaining
        },

        on(event, callback) {
            this.elements.forEach(element => {
                element.addEventListener(event, callback);
            });
            return this;
        },

        off(event, callback) {
            this.elements.forEach(element => {
                element.removeEventListener(event, callback);
            });
            return this;
        },

        trigger(event) {
            this.elements.forEach(element => {
                const evt = new Event(event);
                element.dispatchEvent(evt);
            });
            return this;
        },

        click(callback) {
            if (callback) {
                this.on('click', callback);
            } else {
                this.trigger('click');
            }
            return this;
        },

        hover(enterCallback, leaveCallback) {
            this.on('mouseenter', enterCallback);
            this.on('mouseleave', leaveCallback);
            return this;
        },

        focus() {
            this.elements.forEach(element => {
                element.focus();
            });
            return this;
        },

        blur() {
            this.elements.forEach(element => {
                element.blur();
            });
            return this;
        },

        hide() {
            this.elements.forEach(element => {
                element.style.display = 'none';
            });
            return this;
        },

        show() {
            this.elements.forEach(element => {
                element.style.display = '';
            });
            return this;
        },

        remove() {
            this.elements.forEach(element => {
                element.remove();
            });
            return this;
        },

        attr(name, value) {
            if (value === undefined) {
                return this.elements[0]?.getAttribute(name);
            } else {
                this.elements.forEach(element => {
                    element.setAttribute(name, value);
                });
                return this;
            }
        },

        prop(name, value) {
            if (value === undefined) {
                return this.elements[0]?.[name];
            } else {
                this.elements.forEach(element => {
                    element[name] = value;
                });
                return this;
            }
        },

        css(property, value) {
            if (value === undefined) {
                return window.getComputedStyle(this.elements[0])[property];
            } else {
                this.elements.forEach(element => {
                    element.style[property] = value;
                });
                return this;
            }
        },

        html(content) {
            if (content === undefined) {
                return this.elements[0]?.innerHTML;
            } else {
                this.elements.forEach(element => {
                    element.innerHTML = content;
                });
                return this;
            }
        },

        text(content) {
            if (content === undefined) {
                return this.elements[0]?.textContent;
            } else {
                this.elements.forEach(element => {
                    element.textContent = content;
                });
                return this;
            }
        },

        val(value) {
            if (value === undefined) {
                return this.elements[0]?.value;
            } else {
                this.elements.forEach(element => {
                    element.value = value;
                });
                return this;
            }
        },

        append(content) {
            this.elements.forEach(element => {
                element.insertAdjacentHTML('beforeend', content);
            });
            return this;
        },

        prepend(content) {
            this.elements.forEach(element => {
                element.insertAdjacentHTML('afterbegin', content);
            });
            return this;
        },

        before(content) {
            this.elements.forEach(element => {
                element.insertAdjacentHTML('beforebegin', content);
            });
            return this;
        },

        after(content) {
            this.elements.forEach(element => {
                element.insertAdjacentHTML('afterend', content);
            });
            return this;
        },

        next(selector) {
            const nextElements = [];
            this.elements.forEach(element => {
                const nextSibling = element.nextElementSibling;
                if (nextSibling && nextSibling.matches(selector)) {
                    nextElements.push(nextSibling);
                }
            });
            return JX(nextElements);
        },

        find(selector) {
            const foundElements = [];
            this.elements.forEach(element => {
                const children = element.querySelectorAll(selector);
                children.forEach(child => {
                    foundElements.push(child);
                });
            });
            return JX(foundElements);
        },

        empty() {
            this.elements.forEach(element => {
                element.innerHTML = '';
            });
            return this;
        },

        map(callback) {
            return Array.from(this.elements).map(callback);
        },

        data(key, value) {
            if (value === undefined) {
                return this.elements[0]?.dataset[key];
            } else {
                this.elements.forEach(element => {
                    element.dataset[key] = value;
                });
                return this;
            }
        },

        hasClass(className) {
            return Array.from(this.elements).some(element => element.classList.contains(className));
        },

        is(selector) {
            return Array.from(this.elements).some(element => element.matches(selector));
        },

        not(selector) {
            return new JX(Array.from(this.elements).filter(element => !element.matches(selector)));
        },

        width(value) {
            if (value === undefined) {
                return this.elements[0]?.offsetWidth;
            } else {
                this.elements.forEach(element => {
                    element.style.width = value;
                });
                return this;
            }
        },

        height(value) {
            if (value === undefined) {
                return this.elements[0]?.offsetHeight;
            } else {
                this.elements.forEach(element => {
                    element.style.height = value;
                });
                return this;
            }
        },

        outerWidth() {
            return this.elements[0]?.offsetWidth;
        },

        outerHeight() {
            return this.elements[0]?.offsetHeight;
        },

        offset() {
            const rect = this.elements[0]?.getBoundingClientRect();
            return rect ? { top: rect.top, left: rect.left } : null;
        },

        filter(selector) {
            return new JX(Array.from(this.elements).filter(element => element.matches(selector)));
        },

        first() {
            return new JX([this.elements[0]]);
        },

        last() {
            return new JX([this.elements[this.elements.length - 1]]);
        },

        eq(index) {
            return new JX([this.elements[index]]);
        }
    };
};

$$.errorHandler();