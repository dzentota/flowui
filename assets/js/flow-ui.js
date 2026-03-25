/**
 * FlowUI - Client-side Runtime
 * Lightweight vanilla JS framework for progressive enhancement
 */
(function() {
    'use strict';

    class FlowUI {
        constructor() {
            this.validators = this.initValidators();
            this.components = new Map();
            this.init();
        }

        init() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.hydrate());
            } else {
                this.hydrate();
            }

            this.observeMutations();
        }

        hydrate() {
            this.initForms();
            this.initModals();
            this.initTabs();
            this.initAccordions();
            this.initDropdowns();
            this.initAlerts();
        }

        observeMutations() {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1) {
                            this.hydrateElement(node);
                        }
                    });
                });
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }

        hydrateElement(element) {
            if (element.tagName === 'FORM') {
                this.attachFormValidation(element);
            }
            
            const forms = element.querySelectorAll('form');
            forms.forEach(form => this.attachFormValidation(form));

            const modals = element.querySelectorAll('[data-trigger]');
            modals.forEach(modal => this.initModal(modal));

            const tabs = element.querySelectorAll('[data-tab]');
            if (tabs.length > 0) {
                this.initTabGroup(tabs[0].parentElement);
            }

            const accordions = element.querySelectorAll('[data-ui="accordion"]');
            accordions.forEach(acc => this.initAccordion(acc));
            
            const dropdowns = element.querySelectorAll('[data-toggle="dropdown"]');
            dropdowns.forEach(dropdown => this.initDropdown(dropdown));
            
            const alerts = element.querySelectorAll('[data-alert]');
            alerts.forEach(alert => this.initAlert(alert));
        }

        initForms() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => this.attachFormValidation(form));
        }

        attachFormValidation(form) {
            if (form.dataset.flowValidated) return;
            form.dataset.flowValidated = 'true';

            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                    return false;
                }

                if (form.dataset.ajax === 'true') {
                    e.preventDefault();
                    this.submitFormAjax(form);
                }
            });

            const inputs = form.querySelectorAll('[data-rules]');
            inputs.forEach(input => {
                input.addEventListener('blur', () => {
                    this.validateField(input);
                });
                
                input.addEventListener('input', () => {
                    this.clearFieldError(input);
                });
            });
        }

        validateForm(form) {
            const inputs = form.querySelectorAll('[data-rules]');
            let isValid = true;

            inputs.forEach(input => {
                if (!this.validateField(input)) {
                    isValid = false;
                }
            });

            return isValid;
        }

        validateField(field) {
            const rules = field.dataset.rules;
            if (!rules) return true;

            const value = field.value;
            const rulesArray = rules.split('|');
            let isValid = true;

            this.clearFieldError(field);

            for (const rule of rulesArray) {
                const [ruleName, ...params] = rule.split(':');
                const validator = this.validators[ruleName];

                if (validator && !validator(value, params)) {
                    this.showFieldError(field, this.getErrorMessage(ruleName, field.name, params));
                    isValid = false;
                    break;
                }
            }

            return isValid;
        }

        clearFieldError(field) {
            field.classList.remove('flow-error-field');
            const error = field.parentElement.querySelector('.flow-error');
            if (error) {
                error.remove();
            }
        }

        showFieldError(field, message) {
            field.classList.add('flow-error-field');
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'flow-error';
            errorDiv.textContent = message;
            
            if (field.nextSibling) {
                field.parentElement.insertBefore(errorDiv, field.nextSibling);
            } else {
                field.parentElement.appendChild(errorDiv);
            }
        }

        getErrorMessage(rule, fieldName, params) {
            const messages = {
                required: `The ${fieldName} field is required.`,
                email: `The ${fieldName} must be a valid email address.`,
                min: `The ${fieldName} must be at least ${params[0]} characters.`,
                max: `The ${fieldName} must not exceed ${params[0]} characters.`,
                numeric: `The ${fieldName} must be a number.`,
                alpha: `The ${fieldName} must contain only letters.`,
                alphanumeric: `The ${fieldName} must contain only letters and numbers.`,
                url: `The ${fieldName} must be a valid URL.`,
            };

            return messages[rule] || `The ${fieldName} is invalid.`;
        }

        initValidators() {
            return {
                required: (value) => value.trim() !== '',
                email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
                min: (value, params) => value.length >= parseInt(params[0]),
                max: (value, params) => value.length <= parseInt(params[0]),
                numeric: (value) => !isNaN(value) && !isNaN(parseFloat(value)),
                alpha: (value) => /^[a-zA-Z]+$/.test(value),
                alphanumeric: (value) => /^[a-zA-Z0-9]+$/.test(value),
                url: (value) => {
                    try {
                        new URL(value);
                        return true;
                    } catch {
                        return false;
                    }
                }
            };
        }

        submitFormAjax(form) {
            const formData = new FormData(form);
            const method = form.method || 'POST';
            const action = form.action || window.location.href;
            
            // Add loading state
            form.classList.add('flow-loading');
            const submitBtn = form.querySelector('[type="submit"]');
            const originalText = submitBtn ? submitBtn.textContent : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Loading...';
            }

            fetch(action, {
                method: method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-FlowUI-Ajax': 'true'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.text();
            })
            .then(html => {
                // Determine target
                let target = null;
                
                if (form.dataset.ajaxTarget) {
                    target = document.querySelector(form.dataset.ajaxTarget);
                } else if (form.dataset.ajaxReplace === 'true') {
                    target = form;
                } else {
                    target = form.parentElement;
                }
                
                if (target) {
                    // Replace content
                    if (target === form) {
                        form.outerHTML = html;
                    } else {
                        target.innerHTML = html;
                    }
                    
                    // Dispatch custom event
                    document.dispatchEvent(new CustomEvent('flowui:ajax:success', {
                        detail: { form, target, html }
                    }));
                }
                
                // Remove loading state
                form.classList.remove('flow-loading');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            })
            .catch(error => {
                console.error('FlowUI AJAX Error:', error);
                
                // Dispatch error event
                document.dispatchEvent(new CustomEvent('flowui:ajax:error', {
                    detail: { form, error }
                }));
                
                // Show error alert if possible
                const errorDiv = document.createElement('div');
                errorDiv.className = 'flow-alert flow-alert-error';

                const icon = document.createElement('span');
                icon.className = 'flow-alert-icon';
                icon.textContent = '✕';

                const msg = document.createElement('span');
                msg.className = 'flow-alert-message';
                msg.textContent = 'Request failed: ' + error.message;

                const closeBtn = document.createElement('button');
                closeBtn.className = 'flow-alert-close';
                closeBtn.textContent = '×';
                closeBtn.addEventListener('click', () => errorDiv.remove());

                errorDiv.appendChild(icon);
                errorDiv.appendChild(msg);
                errorDiv.appendChild(closeBtn);
                
                if (form.previousSibling) {
                    form.parentNode.insertBefore(errorDiv, form);
                } else {
                    form.parentNode.insertBefore(errorDiv, form);
                }
                
                // Remove loading state
                form.classList.remove('flow-loading');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
        }

        initModals() {
            const modals = document.querySelectorAll('dialog[data-trigger]');
            modals.forEach(modal => this.initModal(modal));
        }

        initModal(modal) {
            const triggerId = modal.dataset.trigger;
            const trigger = document.getElementById(triggerId);
            
            if (trigger && !trigger.dataset.flowModal) {
                trigger.dataset.flowModal = 'true';
                trigger.addEventListener('click', (e) => {
                    e.preventDefault();
                    modal.showModal();
                });
            }

            if (!modal.dataset.flowModal) {
                modal.dataset.flowModal = 'true';
                
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        modal.close();
                    }
                });

                modal.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        modal.close();
                    }
                });
            }
        }

        initTabs() {
            const tabContainers = document.querySelectorAll('[data-tab]');
            const groupedTabs = new Map();

            tabContainers.forEach(tab => {
                const parent = tab.parentElement;
                if (!groupedTabs.has(parent)) {
                    groupedTabs.set(parent, []);
                }
                groupedTabs.get(parent).push(tab);
            });

            groupedTabs.forEach((tabs, parent) => {
                this.initTabGroup(parent);
            });
        }

        initTabGroup(container) {
            if (container.dataset.flowTabs) return;
            container.dataset.flowTabs = 'true';

            const tabs = container.querySelectorAll('[data-tab]');
            if (tabs.length === 0) return;

            const nav = document.createElement('ul');
            nav.className = 'flow-tabs-nav';
            
            tabs.forEach((tab, index) => {
                const title = tab.dataset.tab;
                const li = document.createElement('li');
                const button = document.createElement('button');
                button.textContent = title;
                button.dataset.tabIndex = index;
                
                if (index === 0) {
                    button.className = 'active';
                    tab.style.display = 'block';
                } else {
                    tab.style.display = 'none';
                }

                button.addEventListener('click', () => {
                    tabs.forEach((t, i) => {
                        t.style.display = i === index ? 'block' : 'none';
                    });
                    nav.querySelectorAll('button').forEach((btn, i) => {
                        btn.className = i === index ? 'active' : '';
                    });
                });

                li.appendChild(button);
                nav.appendChild(li);
            });

            container.insertBefore(nav, tabs[0]);
        }

        initAccordions() {
            const accordions = document.querySelectorAll('[data-ui="accordion"]');
            accordions.forEach(acc => this.initAccordion(acc));
        }

        initAccordion(accordion) {
            if (accordion.dataset.flowAccordion) return;
            accordion.dataset.flowAccordion = 'true';

            const singleOpen = accordion.dataset.singleOpen === 'true';
            const sections = accordion.querySelectorAll('section');

            sections.forEach((section, index) => {
                const header = section.querySelector('header');
                const content = section.querySelector('content');
                
                if (!header || !content) return;

                header.style.cursor = 'pointer';
                content.style.display = 'none';
                
                header.setAttribute('aria-expanded', 'false');
                header.setAttribute('role', 'button');

                header.addEventListener('click', () => {
                    const isOpen = content.style.display === 'block';
                    
                    if (singleOpen) {
                        sections.forEach(s => {
                            const c = s.querySelector('content');
                            const h = s.querySelector('header');
                            if (c) c.style.display = 'none';
                            if (h) h.setAttribute('aria-expanded', 'false');
                        });
                    }

                    content.style.display = isOpen ? 'none' : 'block';
                    header.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
                });
            });
        }

        initDropdowns() {
            const dropdowns = document.querySelectorAll('[data-toggle="dropdown"]');
            dropdowns.forEach(dropdown => this.initDropdown(dropdown));
        }

        initDropdown(trigger) {
            if (trigger.dataset.flowDropdown) return;
            trigger.dataset.flowDropdown = 'true';

            const menuId = trigger.dataset.dropdownMenu;
            if (!menuId) return;

            const menu = document.getElementById(menuId);
            if (!menu) return;

            // Initially hide menu
            menu.style.display = 'none';
            menu.style.position = 'absolute';

            // Toggle dropdown
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const isOpen = menu.style.display === 'block';

                // Close all other dropdowns
                document.querySelectorAll('.flow-dropdown-menu').forEach(m => {
                    if (m !== menu) {
                        m.style.display = 'none';
                        const t = document.querySelector(`[data-dropdown-menu="${m.id}"]`);
                        if (t) t.setAttribute('aria-expanded', 'false');
                    }
                });

                if (isOpen) {
                    menu.style.display = 'none';
                    trigger.setAttribute('aria-expanded', 'false');
                } else {
                    menu.style.display = 'block';
                    trigger.setAttribute('aria-expanded', 'true');
                    this.positionDropdown(trigger, menu);
                }
            });

            // Close on outside click
            document.addEventListener('click', (e) => {
                if (!trigger.contains(e.target) && !menu.contains(e.target)) {
                    menu.style.display = 'none';
                    trigger.setAttribute('aria-expanded', 'false');
                }
            });

            // Close on ESC
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && menu.style.display === 'block') {
                    menu.style.display = 'none';
                    trigger.setAttribute('aria-expanded', 'false');
                }
            });
        }

        positionDropdown(trigger, menu) {
            const rect = trigger.getBoundingClientRect();
            menu.style.top = (rect.bottom + window.scrollY) + 'px';
            menu.style.left = (rect.left + window.scrollX) + 'px';
            menu.style.minWidth = rect.width + 'px';
        }

        initAlerts() {
            const alerts = document.querySelectorAll('[data-alert]');
            alerts.forEach(alert => this.initAlert(alert));
        }

        initAlert(alert) {
            if (alert.dataset.flowAlert) return;
            alert.dataset.flowAlert = 'true';

            // Find close button
            const closeBtn = alert.querySelector('.flow-alert-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                });
            }

            // Auto-dismiss if specified
            const autoDismiss = alert.dataset.autoDismiss;
            if (autoDismiss) {
                const delay = parseInt(autoDismiss) || 5000;
                setTimeout(() => {
                    if (closeBtn) {
                        closeBtn.click();
                    } else {
                        alert.remove();
                    }
                }, delay);
            }
        }

        // AJAX Helper Methods
        load(url, target, options = {}) {
            const targetEl = typeof target === 'string' ? document.querySelector(target) : target;
            
            if (!targetEl) {
                console.error('FlowUI: Target element not found');
                return Promise.reject('Target not found');
            }

            const method = options.method || 'GET';
            const data = options.data || null;
            
            // Add loading state
            targetEl.classList.add('flow-loading');

            const fetchOptions = {
                method: method,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-FlowUI-Ajax': 'true'
                }
            };

            if (data && method !== 'GET') {
                if (data instanceof FormData) {
                    fetchOptions.body = data;
                } else {
                    fetchOptions.body = new URLSearchParams(data);
                }
            }

            return fetch(url, fetchOptions)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    targetEl.innerHTML = html;
                    targetEl.classList.remove('flow-loading');
                    
                    // Dispatch event
                    document.dispatchEvent(new CustomEvent('flowui:loaded', {
                        detail: { url, target: targetEl, html }
                    }));
                    
                    return html;
                })
                .catch(error => {
                    targetEl.classList.remove('flow-loading');
                    console.error('FlowUI Load Error:', error);
                    throw error;
                });
        }

        post(url, data, options = {}) {
            return this.load(url, options.target || document.body, {
                method: 'POST',
                data: data,
                ...options
            });
        }
    }

    window.FlowUI = new FlowUI();
})();
