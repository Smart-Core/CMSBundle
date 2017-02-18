$(document).ready(function () {
    // Отрисовка тулбара
    if (typeof cms_front_controls === 'object') {
        // Отрисовать тулбар.
        if (!$.isEmptyObject(cms_front_controls.toolbar)) {
            renderToolbar();
        }
    }

    function renderToolbar() {
        $('body').css('padding-top', '2.5em')
            .prepend('<div class="cms-tool-bar--new">' +
                '<div class="cms-tool-bar__container">' +
                '<div class="cms-tool-bar__entity cms-tool-bar__menu-button">' +
                '<span></span>' +
                '<span></span>' +
                '<span></span>' +
                '</div>' +
                '<div class="cms-tool-bar__container--left">' +
                '<div class="cms-tool-bar__entity">' +
                '<a href="' + basePath + '"><i class="cms-tool-bar__icon cms-tool-bar__icon--root"></i></a>' +
                '</div>' +
                '</div>' +
                '<div class="cms-tool-bar__container--right">' +
                //'<div class="cms-tool-bar__entity cms-tool-bar__switcher cms-tool-bar__entity--switched">' +
                //'<label class="switch">' +
                //'<input type="checkbox">' +
                //'<div class="slider round">' +
                //'</div>' +
                //'</label>' +
                //'</div>' +
                '</div>' +
                '</div>' +
                '</div>');
    }

    // Переключатель "просмотр" и "редактирование" на тулбаре.

    //$('.switch').click(function() {
      //  if ($('.switch input').prop("checked")) {
            /*
             if ($(this).attr('data-toggle') == 'button') {
             $('.cms-frontadmin-node').unbind('mouseenter mouseleave dblclick');
             $('.cms-empty-node').remove();
             $.removeCookie('cms-frontadmin-mode', { path: '/' });
             } else {
             // $(this).addClass($(this).attr('class-toggle'));
             //$(this).text(cms_front_controls.toolbar.right.eip_toggle[1]);
             */
            $('.cms-frontadmin-node').addClass('cms-frontadmin-node-mode-edit');
            // Включить отрисовку панелей управления нодами.
            $('.cms-frontadmin-node-mode-edit').hover(
                function(){
                    var elem = this;

                    if (typeof cms_front_controls.nodes[$(elem).attr('id')] === 'object') {
                        var node = cms_front_controls.nodes[$(elem).attr('id')];

                        var node_buttons = '<ul class="cms-tool-node">';

                        // сначала поиск действия по умолчанию.
                        $.each(node, function(index, value) {
                            /*
                                if (value.descr != undefined) {
                                    var button_title = value.descr;
                                } else {
                                    var button_title = '';
                                }
                            */
                            /*
                            if (value.default == true) {
                                node_buttons += '<li><a OnClick="window.location=\'' + value.uri + '?redirect_to=' + window.location.pathname + window.location.search
                                    + '\'" class="cms-tool-node"></a></li>';
                            }
                            */
                        });

                        //node_buttons += '<button data-toggle="dropdown" class="btn btn-mini btn-xs dropdown-toggle"><span class="caret"></span></button>';
                        //node_buttons += '<ul class="dropdown-menu">';

                        // затем отрисовка пунктов меню.
                        $.each(node, function(index, value) {

                            /*
                                if (value.descr != undefined) {
                                    var item_title = value.descr;
                                } else {
                                    var item_title = '';
                                }
                            */

                            if (value.default == true) {
                                node_buttons += '<li><a style="background-color: white;" href="' + value.uri + '?redirect_to=' + window.location.pathname + window.location.search + '">' + '<i style="height: 24px; width: 24px; display: block;" class="cms-tool-bar__icon cms-tool-bar__icon--edit-module"></i>' + '</a>';
                            } else {
                                node_buttons += '<li><a style="background-color: white;" href="' + value.uri + '?redirect_to=' + window.location.pathname + window.location.search + '">' + '<i style="height: 24px; width: 24px; display: block;" class="cms-tool-bar__icon cms-tool-bar__icon--module"></i>' + '</a>';
                            }

                            /*
                            if (value.default == true) {
                                node_buttons += '<strong>' + value.title + '</strong></a></li>';
                            } else {
                                node_buttons += value.title + '</a></li>';
                            }
                            */
                        });

                        node_buttons += '</ul>';

                        $(elem).prepend(node_buttons);
                    }
                },
                function(){
                    var elem = this;
                    $(elem).find('.cms-tool-node').hide().remove();
                }
            );

            // заглушки для пустых нод.
            $('.cms-frontadmin-node').each(function() {
                if( $.trim($(this).text()) == "" ){
                    $(this).append('<div class="cms-empty-node"></div>');
                }
            });

      //      $.cookie('cms-frontadmin-mode', 'edit', { path: '/' }); // @todo настройку path в корень сайта.
       // } else {
       //     $('.cms-frontadmin-node').removeClass('cms-frontadmin-node-mode-edit');
        //    $('.cms-frontadmin-node').children('.cms-empty-node').remove();
       // }
        //}
        //}
   // });

// Элементы справа
    if (typeof cms_front_controls.toolbar.right === 'object') {
        $.each(cms_front_controls.toolbar.right, function (index, value) {
            var item =
                '<div class="cms-tool-bar__entity';

            if (typeof value.items === 'object') {
                item+= ' cms-tool-bar__entity--dropdown'
            }

            item += '">';

            if (value.uri === undefined) {
                item+= '<a href="#nolink"><i class="cms-tool-bar__icon cms-tool-bar__icon--large cms-tool-bar__icon--' + value.icon + '"></i>';
            } else {
                item+= '<a href="' + value.uri + '"><i class="cms-tool-bar__icon cms-tool-bar__icon--large cms-tool-bar__icon--' + value.icon + '"></i>';
            }

            if (value.onlyicon === true) {
                item+='</a>';
            } else {
                item+='<span>' + value.title + '</span></a>';
            }

            if (typeof value.items === 'object') {
                item+= '<ul class="dropdown-content">';
                $.each(value.items, function (index2, value2) {
                    item+= '<li><a href="' + value2.uri + '"><i class="cms-tool-bar__icon cms-tool-bar__icon--'+ value2.icon +'"></i>' + value2.title + '</a></li>';
                });
            }
            $('.cms-tool-bar__container--right').append(item + '</div></div>');
        });
    }

    //уведомления
    /*
     if (typeof cms_front_controls.toolbar.notifications === 'object') {
     var count = Object.keys(cms_front_controls.toolbar.notifications).length;
     if (count > 0) {

     var notify = '<div class="cms-tool-bar__entity cms-tool-bar__entity--counter cms-tool-bar__entity--only-icon cms-tool-bar__entity--bell"><a href="#test"><span>' + count + '</span></a></div>';

     item += '<ul class="dropdown-menu">';
     $.each(cms_front_controls.toolbar.notifications, function (index, value) {
     if (typeof value === 'object') {
     $.each(value, function (index2, value2) {
     console.log(value2);
     item += '<li><a href="' + value2.url + '">' + value2.title;
     if (value2.count > 0) {
     item += ' <span class="label label-danger label-important">' + value2.count + '</span>';
     }
     item += '</a></li>';
     });
     }
     });
     item += '</ul>';

     $('body .cms-tool-bar .cms-tool-bar__container .cms-tool-bar__container--right').prepend(notify);
     }
     }
     */

    // cлева
    if (typeof cms_front_controls.toolbar.left === 'object') {
        $.each(cms_front_controls.toolbar.left, function (index, value) {
            var item =
                '<div class="cms-tool-bar__entity';

            if (typeof value.items === 'object') {
                item+= ' cms-tool-bar__entity--dropdown'
            }

            item += '">';

            if (value.uri === undefined) {
                item+= '<a href="#nolink"><i class="cms-tool-bar__icon cms-tool-bar__icon--large cms-tool-bar__icon--' + value.icon + '"></i>';
            } else {
                item+= '<a href="' + value.uri + '"><i class="cms-tool-bar__icon cms-tool-bar__icon--large cms-tool-bar__icon--' + value.icon + '"></i>';
            }

            if (value.onlyicon === true) {
                item+='</a>';
            } else {
                item+='<span>' + value.title + '</span></a>';
            }

            if (typeof value.items === 'object') {
                item+= '<ul class="dropdown-content">';
                $.each(value.items, function (index2, value2) {
                    item+= '<li><a href="' + value2.uri + '"><i class="cms-tool-bar__icon cms-tool-bar__icon--'+ value2.icon +'"></i>' + value2.title + '</a></li>';
                });
            }

            item += '</ul></div>';

            if (value.icon === undefined) {
                item = '';
            }
            $('.cms-tool-bar__container--left').append(item);
        });
    }

});

