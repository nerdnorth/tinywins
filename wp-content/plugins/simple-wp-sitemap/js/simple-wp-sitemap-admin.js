(function ($) {
    var Sitemap = {

        // Constructor function
        run: function (config) {
            var self = this;
            this.c = config;

            this.c.menuBtns.each(function (i, btn) {
                btn = $(btn);

                if (self.c.activePage.val() === btn.attr('id')) {
                    self.changeMenuState(btn);
                }
            });

            this.bindEvents();
        },

        // Binds all events
        bindEvents: function () {
            var self = this;

            this.c.menuBtns.on('click', function () {
                self.changeMenuState($(this));
            });

            this.c.orderList.on('click', function (e) {
                self.changeOrderItem($(e.target));
            });

            this.c.defaultBtn.on('click', function () {
                self.restoreDefaultOrder();
            });

            this.c.form.on('submit', function (e) {
                e.preventDefault();
                self.submitForm();
            });

            this.c.premiumBtn.on('click', function () {
                self.upgrade();
            });

            this.c.premiumInput.on('keypress', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    self.upgrade();
                }
            });

            this.c.error.on('click', function (e) {
                if (!$(e.target).is('a')) {
                    $(this).text('');
                }
            });
        },

        // Changes menu state and active page
        changeMenuState: function (btn) {
            this.c.menuBtns.attr('class', '');
            btn.attr('class', 'sitemap-active');

            this.c.tables.attr('id', '')
                .parent()
                .find('table[data-id="' + btn.attr('id') + '"]')
                .attr('id', 'sitemap-table-show');

            this.c.activePage.val(btn.attr('id'));
        },

        // Changes an item in order menu
        changeOrderItem: function (node) {
            var li = node.parent(), elem;

            if (node.attr('class') === 'sitemap-up' && li.prev()[0]) {
                li.prev().before(li);

            } else if (node.attr('class') === 'sitemap-down' && li.next()[0]) {
                li.next().after(li);

            } else if (node.hasClass('sitemap-change-btn')) {
                elem = li.find('.swp-name');

                if (node.val() === 'Change') {
                    elem.replaceWith('<input type="text" class="swp-name" value="' + this.esc(elem.text()) + '" data-name="' + elem.attr('data-name') + '">');
                    node.val('Ok');
                } else {
                    elem.replaceWith('<span class="swp-name" data-name="' + elem.attr('data-name') + '">' + this.esc(elem.val()) + '</span>');
                    node.val('Change');
                }
            }
        },

        // Sets hidden fields values and submits the form to save changes
        submitForm: function () {
            var inputs = this.c.orderList.find('input[type=hidden]'),
                titles = this.c.orderList.find('[data-name]'),
                self = this;

            $.each(inputs, function (i, node) {
                inputs.eq(i).val(self.esc((i + 1) + '-|-' + (titles.eq(i).text() || titles.eq(i).val())));
            });
            this.c.form[0].submit();
        },

        // Submits form to upgrade plugin to premium
        upgrade: function () {
            this.c.premiumForm.find('input[type=hidden]').attr({
                name: 'upgrade_to_premium',
                value: this.esc(this.c.premiumInput.val())
            });
            this.c.premiumForm.submit();
        },

        // Restores default order options
        restoreDefaultOrder: function () {
            var sections = ['Home', 'Posts', 'Pages', 'Other', 'Categories', 'Tags', 'Authors'],
                html = '';

            $.each(sections, function (i) {
                html += '<li><span class="swp-name" data-name="' + sections[i].toLowerCase() + '">' + sections[i] + '</span>' +
                    '<span class="sitemap-down" title="move down"></span><span class="sitemap-up" title="move up"></span>' +
                    '<input type="hidden" name="simple_wp_' + sections[i].toLowerCase() + '_n" value="' + (i + 1) + '">' +
                    '<input type="button" value="Change" class="button-secondary sitemap-change-btn"></li>';
            });
            this.c.orderList.html(html);
            this.c.updatedText.val('');
        },

        // Escapes some special chars
        esc: function (str) {
            return str.replace(/[<"'>]/g, function (ch) {
                return {'<': "&lt;", '>': "&gt;", '"': '&quot;', '\'': '&#39;'}[ch];
            });
        }
    };

    Sitemap.run({
        tables: $('#simple-wp-sitemap-form table'),
        updatedText: $('#simple_wp_last_updated'),
        textarea: $('#swsp-add-pages-textarea'),
        activePage: $('#simple_wp_active-page'),
        orderList: $('#sitemap-display-order'),
        premiumForm: $('#simpleWpHiddenForm'),
        menuBtns: $('#sitemap-settings li'),
        form: $('#simple-wp-sitemap-form'),
        premiumBtn: $('#upgradeToPremium'),
        defaultBtn: $('#sitemap-defaults'),
        premiumInput: $('#upgradeField'),
        error: $('#swpErrorText'),
    });
})(jQuery);
