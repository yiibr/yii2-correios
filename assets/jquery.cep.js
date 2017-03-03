/*!
 * jQuery CEP
 * Released under the MIT license.
 */
(function ($) {
    var Cep = function ($element, options) {
        this.init($element, options);
    };

    Cep.prototype = {
        constructor: Cep,

        init: function ($element, options) {
            this.$element = $element;
            this.$root = this.$element.parent().parent();
            this.$window = this.$root.find('.modal');
            this.action = options.action;
            this.fields = options.fields;
            this.queryParam = options.queryParam;

            var that = this;

            this.$element.parent().find('span a:first').on('click', function(){
                that.search($(this), true);
            });
            this.$root.find('.modal span a:first').on('click', function(){
                that.search($(this), false);
            });
            this.$window.find('input:first').on('keydown', function(e){
                if (e.which == '13') {
                    e.preventDefault();
                    $(this).parent().find('span a:first').trigger('click');
                }
            });
        },

        _assign: function (data) {
            if (!$.isEmptyObject(data)) {
                this.$element.trigger($.Event('beforeAssignData'), [data]);
                for (var prop in this.fields) {
                    var $input = jQuery('#' + this.fields[prop]);
                    if ($input.length) {
                        if (prop in data) {
                            $input.val(data[prop]);
                        } else {
                            $input.val('');
                        }
                    }
                }
                this.$element.trigger($.Event('afterAssignData'), [data]);
            }
        },

        _locate: function (data) {
            var $tbody = this.$window.find('tbody'),
                that = this;

            $tbody.empty();
            for (var i in data) {
                var row = data[i];
                var tr = $(
                    '<tr>' +
                    '<td><a href="#">' + row.cep + '</a></td><td>' + row.location + '</td><td>' + row.district + '</td>' +
                    '<td>' + row.city + '</td><td>' + row.state + '</td>' +
                    '</tr>'
                ).appendTo($tbody);
                tr.data('address', row);
            }

            $tbody.find('a').on('click', function () {
                var address = $(this).parent().parent().data('address');
                that._assign(address);
                that.$element.find('input:first').val(address.cep);
                that.$window.modal('hide');
            });
        },

        search: function ($button, cep) {
            var $input = cep ? this.$element : this.$window.find('input:first'),
                val = $input.val(),
                params = {},
                that = this;

            if (cep) {
                if (!val) {
                    var modal = this.$window.data("modal");
                    if (!modal) {
                        this.$window.modal({keyboard: false});
                    }
                    this.$window.modal('show');
                    return;
                }
                val = val.replace(/[^0-9]/g, '');
            }

            if (val) {
                params[that.queryParam] = val;
                $.get(this.action, params, function (data) {
                    if (cep) {
                        that._assign(data[0]);
                    } else {
                        that._locate(data);
                    }
                }).fail(function (xhr, status, text) {
                    var error = $.parseJSON(xhr.responseText);
                    window.alert((error.hasOwnProperty('message') && error.message) ? error.message : text);
                });
            }
        }
    };

    $.fn.cep = function(options){
        return this.each(function(){
            var $this = $(this),
                data = $this.data('cep');

            if (!data) {
                $this.data('cep', new Cep($this, options));
            }
        });
    };

})(jQuery);
