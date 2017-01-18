<?php defined('ABSPATH') || exit;
/*
 * Simple Wp Sitemap Admin interface
 */
?>
<div class="wrap">

    <h2 id="simple-wp-sitemap-h2">
        <img src="<?php printf('%ssign.png', $ops->pluginUrl()); ?>" alt="logo" width="40" height="40">
        <span>Simple Wp Sitemap settings</span>
    </h2>

    <p>Your two sitemaps are active! Here you can change and customize them.</p>
    <p><strong>Links to your xml and html sitemap:</strong></p>

    <ul>
        <li>Xml sitemap: <?php printf('<a href="%1$s">%1$s</a>', $ops->sitemapUrl('xml')); ?></li>
        <li>Html sitemap:
            <?php if ($ops->getOption('simple_wp_block_html')) {
                echo '(disabled)';
            } else {
                printf('<a href="%1$s">%1$s</a>', $ops->sitemapUrl('html'));
            } ?>
        </li>
    </ul>

    <noscript>(Please enable javascript to edit options)</noscript>

    <form method="post" action="<?php echo $ops->getSubmitUrl(); ?>" id="simple-wp-sitemap-form">

        <?php settings_fields('simple_wp-sitemap-group'); ?>

        <ul id="sitemap-settings">
            <li id="sitemap-normal" class="sitemap-active">General</li>
            <li id="sitemap-advanced">Order</li>
            <li id="sitemap-premium">Premium</li>
        </ul>

        <input type="hidden" id="simple_wp_active-page" name="simple_wp_active_page" value="<?php echo $ops->getPage(); ?>">

        <table id="sitemap-table-show" class="widefat form-table table-hidden" data-id="sitemap-normal">
            <tr>
                <td>
                    <strong>Add pages</strong>
                </td>
            </tr>
            <tr>
                <td>
                    Add pages to the sitemaps in addition to your normal wordpress ones. Just paste "full" urls in the textarea like:
                    <strong>http://www.example.com/a-page/</strong>. Each link on a new row <em>(this will affect both your xml and html sitemap)</em>.
                </td>
            </tr>
            <tr>
                <td>
                    <textarea rows="7" name="simple_wp_other_urls" placeholder="http://www.example.com/a-page/" class="large-text code" id="swsp-add-pages-textarea"><?php echo $ops->getOption('simple_wp_other_urls'); ?></textarea>
                </td>
            </tr>

            <tr><td><hr></td></tr>

            <tr>
                <td>
                    <strong>Block pages</strong>
                </td>
            </tr>
            <tr>
                <td>
                    Add pages you want to block from showing up in the sitemaps. Same as above, just paste every link on a new row.
                    <em>(Hint: copy paste links from one of the sitemaps to get correct urls)</em>.
                </td>
            </tr>
            <tr>
                <td>
                    <textarea rows="7" name="simple_wp_block_urls" placeholder="http://www.example.com/block-this-page/" class="large-text code"><?php echo $ops->getOption('simple_wp_block_urls'); ?></textarea>
                </td>
            </tr>

            <tr><td><hr></td></tr>

            <tr>
                <td>
                    <strong>Extra sitemap includes</strong>
                </td>
            </tr>
            <tr>
                <td>
                    Check if you want to include categories, tags and/or author pages in the sitemaps.
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="simple_wp_disp_categories" id="simple_wp_cat" <?php echo $ops->getOption('simple_wp_disp_categories'); ?>><label for="simple_wp_cat"> Include categories</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="simple_wp_disp_tags" id="simple_wp_tags" <?php echo $ops->getOption('simple_wp_disp_tags'); ?>><label for="simple_wp_tags"> Include tags</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="simple_wp_disp_authors" id="simple_wp_authors" <?php echo $ops->getOption('simple_wp_disp_authors'); ?>><label for="simple_wp_authors"> Include authors</label>
                </td>
            </tr>

            <tr><td><hr></td></tr>

            <tr>
                <td>
                    <strong>Html sitemap</strong>
                </td>
            </tr>
            <tr>
                <td>
                    Enable or disable your html sitemap. This will not effect your xml sitemap.
                </td>
            </tr>
            <tr>
                <td>
                    <select name="simple_wp_block_html" id="simple_wp_block_html">
                        <?php foreach (array('Enable' => '', 'Disable' => '1', 'Disable and set to 404' => '404') as $key => $val) {
                            printf('<option value="%s"%s>%s</option>', $val, $val == $ops->getOption('simple_wp_block_html') ? ' selected' : '', $key);
                        } ?>
                    </select>
                </td>
            </tr>

            <tr><td><hr></td></tr>

            <tr>
                <td>
                    <strong>Like the plugin?</strong>
                </td>
            </tr>
            <tr>
                <td>
                    Show your support by rating the plugin at wordpress.org, and/or by adding an attribution link to the sitemap.html file :)
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="simple_wp_attr_link" id="simple_wp_check" <?php echo $ops->getOption('simple_wp_attr_link'); ?>><label for="simple_wp_check"> Add "Generated by Simple Wp Sitemap" link at bottom of sitemap.html.</label>
                </td>
            </tr>
            <tr>
                <td>
                    A donation is also always welcome!
                    <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=UH6ANJA7M8DNS" id="simple-wp-sitemap-donate" target="_blank">
                        <img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" alt="PayPal - The safer, easier way to pay online!">
                    </a>
                </td>
            </tr>
        </table><!-- sitemap-normal -->

        <table class="widefat form-table table-hidden" data-id="sitemap-advanced">
            <tr>
                <td>
                    <strong>Display order &amp; titles</strong>
                </td>
            </tr>
            <tr>
                <td>
                    If you want to change the display order in your sitemaps, click the arrows to move sections up or down. They will be displayed as ordered below <em>(highest up is displayed first and lowest down last)</em>.<br><br>
                    Hit the "Change" buttons to change the title displayed in the sitemaps.
                </td>
            </tr>
            <tr>
                <td>
                    <ul id="sitemap-display-order">
                        <?php if (!($orderArray = $ops->getOption('simple_wp_disp_sitemap_order'))) {
                            $orderArray = $ops->getDefaultOrder();
                        }
                        foreach ($orderArray as $key => $val) {
                            printf(
                                '<li><span class="swp-name" data-name="%s">%s</span><span class="sitemap-down" title="move down"></span><span class="sitemap-up" title="move up"></span>' .
                                '<input type="hidden" name="simple_wp_%s_n" value="%d"><input type="button" value="Change" class="button-secondary sitemap-change-btn"></li>',
                                $key, $val['title'], $key, $val['i']
                            );
                        } ?>
                    </ul>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Last updated text:</strong>
                    <input type="text" name="simple_wp_last_updated" placeholder="Last updated" value="<?php echo $ops->getOption('simple_wp_last_updated'); ?>" id="simple_wp_last_updated">
                </td>
            </tr>
            <tr>
                <td>
                    <input type="button" id="sitemap-defaults" class="button-secondary" title="Restore the default display order" value="Restore defaults">
                </td>
            </tr>
        </table><!-- sitemap-advanced -->

        <table class="widefat form-table table-hidden" data-id="sitemap-premium">
            <tr>
                <td>
                    <strong>Simple Wp Sitemap Premium</strong>
                </td>
            </tr>
            <tr>
                <td>
                    Premium is an enhanced version of Simple Wp Sitemap and includes:
                </td>
            </tr>
            <tr>
                <td>
                    <ul class="simple-wp-sitemap-includes">
                        <li>Image sitemaps</li>
                        <li>Split sitemaps into multiple files</li>
                        <li>Add your logo</li>
                        <li>Custom css</li>
                        <li>Color picker</li>
                        <li>Exclude directories</li>
                        <li>And much more!</li>
                    </ul>
                </td>
            </tr>

            <tr><td><hr></td></tr>

            <tr>
                <td>
                    <strong>If you have a premium code, enter it here to upgrade</strong><br><br>
                    <input type="text" id="upgradeField" value="<?php echo $ops->getPosted(); ?>"><span class="button-secondary" id="upgradeToPremium">Upgrade</span>
                    <span id="swpErrorText"><?php echo $ops->getError(); ?></span>
                </td>
            </tr>
            <tr>
                <td>
                    <hr><br><strong>Available at</strong>: <a target="_blank" href="https://www.webbjocke.com/downloads/simple-wp-sitemap-premium/">webbjocke.com/downloads/simple-wp-sitemap-premium</a>
                </td>
            </tr>
        </table><!-- sitemap-premium -->

        <p class="submit"><input type="submit" class="button-primary" value="Save Changes"></p>

        <p><em>(If you have a caching plugin, you might have to clear cache before changes will be shown in the sitemaps)</em></p>

    </form>

    <form method="post" action="<?php echo $ops->getSubmitUrl(); ?>" class="table-hidden" id="simpleWpHiddenForm">
        <input type="hidden">
    </form>

</div><!-- wrap -->
