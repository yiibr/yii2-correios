var Cep = function(options) {
    this.init(options);
};

Cep.prototype = {

    init: function(options) {
        this.$widget = options.widget;
        this.$window = this.$widget.parent().find('.modal');
        this.action = options.action;
        this.fields = options.fields;
    },

    _assign: function(data) {
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
    },

    _locate: function (data){
        var $tbody = this.$window.find('tbody'),
            that = this;

        $tbody.empty();
        for (var i in data) {
            var row = data[i];
            var tr = $(
                '<tr>'+
                '<td><a href="#">'+ row.cep +'</a></td><td>'+ row.location +'</td><td>'+ row.district +'</td>'+
                '<td>'+ row.city +'</td><td>'+ row.state +'</td>'+
                '</tr>'
            ).appendTo($tbody);
            tr.data('address', row);
        }

        $tbody.find('a').on('click', function(){
            var address = $(this).parent().parent().data('address');
            that._assign(address);
            that.$widget.find('input:first').val(address.cep);
            that.$window.modal('hide');
        });
    },

    search: function($button, cep) {
        var $input = $button.parent().parent().children('input:first'),
            val = $input.val(),
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
            val = val.replace(/[^0-9]/g,'');
        }

        $.get(this.action + '&q=' + val ,function( data ) {
            if (cep) {
                that._assign(data[0]);
            } else {
                that._locate(data);
            }
        }).fail(function(xhr, status) {
            alert('Endereço não encontrado!');
        });
    }
};