(function() {
    "use strict";

    window.TS = {
        registerModule: function(name, ob, delayed) {
            if (_dom_is_ready) return TS.error('module "' + name + '" must be registered on before dom ready');
            if (_modules[name]) return TS.error('module "' + name + '" already exists');
            var namespace = _registerInNamespace(name, ob, "module");
            if (namespace === undefined) {
                TS.error('module "' + name + '" cannot be registered after delay; "' + name.split(".").slice(0, -1).join(".") + '" is not registered')
                return
            }
            ob._name = name;
            _modules[name] = ob
        }
    };
    var _registerInNamespace = function(namespace, ob, type) {
        var name = namespace;
        var current_namespace = TS;
        var parts = name.split(".");
        var len = parts.length - 1;
        if (len >= 3) {
            TS.error(type + ' "' + name + '" cannot be registered, as we only support a depth of two sub modules right now')
        } else if (len) {
            name = parts[len];
            var index = 0;
            for (index; index < len; index += 1) {
                if (!parts[index]) {
                    TS.error(type + ' "' + namespace + '" cannot be registered because of a bad name')
                }
                current_namespace = current_namespace[parts[index]];
                if (current_namespace === undefined) {
                    return current_namespace
                }
            }
        }
        if (current_namespace[name] !== undefined) {
            TS.error(type + ' "' + namespace + '" cannot be registered; "' + name + '" already exists on "' + (current_namespace._name || "TS") + '"')
        } else {
            current_namespace[name] = ob
        }
        return current_namespace
    };

    var _dom_is_ready = false;
    var _modules = {};

    TS.registerModule("ui", {
        onStart: function() {}
    });
    TS.registerModule("web", {
        onStart: function() {}
    });
    TS.registerModule("web.pricing", {
        bindUI: function() {
            if ($(".pricing_tabs").length) {
                $(".tab").on("click", _handleTabClick);
                $(document).on("scroll", function() {
                    if ($(window).width() <= 768) {
                        var scroll_position = $(".pricing_tabs").position().top + $(".pricing_tabs").height() - $(".tab_menu").height();
                        if ($(this).scrollTop() >= scroll_position) {
                            $(".tab_menu").hide()
                        } else {
                            $(".tab_menu").show()
                        }
                        if (!$(".is_signed_in").length && $(this).scrollTop() >= 50) {
                            $(".tab_menu").addClass("hide_mobile_menu")
                        } else {
                            $(".tab_menu").removeClass("hide_mobile_menu")
                        }
                    }
                });
                $(".tab_teams").on("click", function() {
                    if ($(this).hasClass("active")) return;
                });
                $(".tab_enterprise").on("click", function() {
                    if ($(this).hasClass("active")) return;
                })
            }

            $(".back_to_the_top .btn").on("click", function(e) {
                $("html, body").animate({
                    scrollTop: 0
                }, 500);
                e.preventDefault()
            });
        }
    });

    function _handleTabClick() {
        $("html, body").animate({
            scrollTop: 0
        }, 500);
    }

    TS.registerModule("ui.tabs", {
        instances: [],
        onStart: function() {},
        create: function(element, config) {
            if (!element) {
                return false
            }
            return new Tabs(element, config)
        }
    });

    function Tabs(element, config) {
        if (typeof element === "undefined") {
            return null
        }
        this.element = element;
        var _tabs = _retrieveTabs(element);
        var _panels = _retrievePanels(_tabs);
        this.element.on("click", ".tab", function(e) {
            e.preventDefault();
            return _activate(this, _tabs, _panels).bind
        });
        this.unbind = function() {
            this.element.off();
            this.element = null;
            _tabs = null;
            _panels = null
        };
        this.element.one("remove", this.unbind);
        return this
    }

    Tabs.prototype.destroy = function() {
        this.element.off();
        this.element.remove()
    };

    function _retrievePanels(tabs) {
        if (typeof tabs === "undefined") {
            return []
        }
        return tabs.map(function(index, item) {
            return $(item).attr("href")
        }).map(function(index, id) {
            return $(id)
        })
    }

    function _retrieveTabs(element) {
        if (typeof element === "undefined") {
            return []
        }
        return element.find(".tab").map(function(index, item) {
            return $(item)
        })
    }

    function _activate(tab, tabs, panels) {
        if (typeof tab === "undefined") {
            return false
        }
        panels.map(function(index, panel_item) {
            return $(panel_item).removeClass("active")
        });
        tabs.map(function(index, tab_item) {
            return $(tab_item).removeClass("active")
        });
        var $tab = $(tab);
        $tab.addClass("active");
        var $target = $($tab.attr("href"));
        var result = $target.addClass("active");
        $tab = null;
        $target = null;
        return result
    }
})();
