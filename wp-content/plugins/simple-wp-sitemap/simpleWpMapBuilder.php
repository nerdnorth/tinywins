<?php defined('ABSPATH') || exit;

/*
 * The sitemap creating class
 */

class SimpleWpMapBuilder {
    public $home = null;
    private $xml = false;
    private $html = false;
    private $posts = '';
    private $pages = '';
    private $blockedUrls = null;
    private $url;
    private $tags;
    private $other;
    private $homeUrl;
    private $authors;
    private $pattern;
    private $categories;
    private $lastUpdated;

    // Constructor
    public function __construct () {
        $this->homeUrl = esc_url(home_url('/'));
        $this->url = esc_url(plugins_url() . '/simple-wp-sitemap/');
        $this->categories = get_option('simple_wp_disp_categories') ? array(0 => 0) : false;
        $this->tags = get_option('simple_wp_disp_tags') ? array(0 => 0) : false;
        $this->authors = get_option('simple_wp_disp_authors') ? array(0 => 0) : false;
        $this->blockedUrls = get_option('simple_wp_block_urls');
        @date_default_timezone_set(get_option('timezone_string'));
    }

    // Generates an xml or html sitemap
    public function generateSitemap ($type) {
        if ($type === 'xml' || $type === 'html') {
            $this->$type = true;
            $this->pattern = ($this->xml ? 'Y-m-d\TH:i:sP' : 'Y-m-d H:i');
            $this->other = $this->getOtherPages();
            $this->setUpBlockedUrls();
            $this->setLastUpdated();
            $this->generateContent();
            $this->printOutput();
        }
    }

    // Returns custom urls user has added
    public function getOtherPages () {
        $xml = '';
        if ($options = get_option('simple_wp_other_urls')) {
            foreach ($options as $option) {
                if ($option && is_array($option)) {
                    if (!is_int($option['date'])) { // fix for old versions of the plugin when date was stored in clear text
                        $option['date'] = strtotime($option['date']);
                    }
                    $xml .= $this->getXml(esc_url($option['url']), date($this->pattern, $option['date']));
                }
            }
        }
        return $xml;
    }

    // Sets up blocked urls into an array
    public function setUpBlockedUrls () {
        if (($blocked = get_option('simple_wp_block_urls')) && is_array($blocked)) {
            $this->blockedUrls = array();
            foreach ($blocked as $block) {
                $this->blockedUrls[$block['url']] = true;
            }
        }
    }

    // Sets the "last updated" text
    public function setLastUpdated () {
        $this->lastUpdated = ($updated = get_option('simple_wp_last_updated')) ? esc_html($updated) : 'Last updated';
    }

    // Matches url against blocked ones that shouldn't be displayed
    public function isBlockedUrl($url) {
        return $this->blockedUrls && isset($this->blockedUrls[$url]);
    }

    // Returns xml or html
    public function getXml ($link, $date) {
        if ($this->xml) {
            return "<url>\n\t<loc>$link</loc>\n\t<lastmod>$date</lastmod>\n</url>\n";
        } else {
            return "<li><a href=\"$link\"><span class=\"link\">$link</span><span class=\"date\">$date</span></a></li>";
        }
    }

    // Querys the database and gets the actual sitemaps content
    public function generateContent () {
        $q = new WP_Query(array('post_type' => 'any', 'post_status' => 'publish', 'posts_per_page' => 50000, 'has_password' => false));

        if ($q->have_posts()) {
            while ($q->have_posts()) {
                $q->the_post();

                $link = esc_url(get_permalink());
                $date = get_the_modified_date($this->pattern);
                $this->getCategoriesTagsAndAuthor($date);

                if (!$this->isBlockedUrl($link)) {
                    if (!$this->home && $link === $this->homeUrl) {
                        $this->home = $this->getXml($link, $date);

                    } elseif (get_post_type() === 'page') {
                        $this->pages .= $this->getXml($link, $date);

                    } else { // posts (also all custom post types are added here)
                        $this->posts .= $this->getXml($link, $date);
                    }
                }
            }
        }
        wp_reset_postdata();
    }

    // Gets a posts categories, tags and author, and compares for last modified date
    public function getCategoriesTagsAndAuthor ($date) {
        if ($this->categories && ($postCategories = get_the_category())) {
            foreach ($postCategories as $category) {
                if (!isset($this->categories[($id = $category->term_id)]) || $this->categories[$id] < $date) {
                    $this->categories[$id] = $date;
                }
            }
        }
        if ($this->tags && ($postTags = get_the_tags())) {
            foreach ($postTags as $tag) {
                if (!isset($this->tags[($id = $tag->term_id)]) || $this->tags[$id] < $date) {
                    $this->tags[$id] = $date;
                }
            }
        }
        if ($this->authors && ($id = get_the_author_meta('ID'))) {
            if (!isset($this->authors[$id]) || $this->authors[$id] < $date) {
                $this->authors[$id] = $date;
            }
        }
    }

    // Prints all output
    public function printOutput () {
        if ($this->xml) {
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<?xml-stylesheet type=\"text/css\" href=\"", $this->url, "css/xml.css\"?>\n<urlset xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9\n\thttp://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\" xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
            $this->sortAndPrintSections();
            echo '</urlset>';

        } else {
            $name = esc_html(get_bloginfo('name'));
            echo '<!doctype html><html lang="', get_locale(), '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>', $name, ' Html Sitemap</title><link rel="stylesheet" href="', $this->url, 'css/html.css"></head><body><div id="wrapper"><h1><a href="', $this->homeUrl, '">', $name, '</a> Html Sitemap</h1>';
            $this->sortAndPrintSections();
            echo '</div></body></html>';
        }
    }

    // Sorts and prints the content sections
    public function sortAndPrintSections () {
        $orderArray = get_option('simple_wp_disp_sitemap_order');

        if (!$orderArray || !isset($orderArray['home'])) {
            require_once 'simpleWpMapOptions.php';
            $ops = new SimpleWpMapOptions();
            $orderArray = $ops->migrateFromOld();
        }
        if (!$this->home) {
            $this->home = $this->getXml($this->homeUrl, date($this->pattern));
        }

        foreach ($orderArray as $key => $arr) {
            $this->printSection($key, $arr);
        }
        $this->attributionLink();
    }

    // Prints a sections xml/html
    public function printSection ($title, $arr) {
        if ($this->$title) {
            if (in_array($title, array('categories', 'tags', 'authors'))) {
                $this->$title = $this->getMetaLinks($title);
            }
            if ($this->$title) {
                if ($this->html) {
                    if ($arr['title'] === 'Authors' && count($this->authors) <= 2) {
                        $arr['title'] = 'Author';
                    }
                    echo '<div class="header"><p class="header-txt">', esc_html($arr['title']), '</p><p class="header-date">', $this->lastUpdated, '</p></div><ul>', $this->$title, '</ul>';
                } else {
                    echo $this->$title;
                }
                $this->$title = null;
            }
        }
    }

    // Displays attribution link if user has checked the checkbox
    public function attributionLink () {
        if ($this->html && get_option('simple_wp_attr_link')) {
            echo '<p id="attr"><a id="attr-a" href="http://www.webbjocke.com/simple-wp-sitemap/" target="_blank" title="http://www.webbjocke.com/simple-wp-sitemap/">Generated by: Simple Wp Sitemap</a></p>';
        }
    }

    // Gets categories, tags and author links
    public function getMetaLinks ($title) {
        $xml = '';
        if ($this->$title) {
            foreach ($this->$title as $id => $date) {
                if ($date) {
                    switch ($title) {
                        case 'tags': $link = esc_url(get_tag_link($id)); break;
                        case 'categories': $link = esc_url(get_category_link($id)); break;
                        default: $link = esc_url(get_author_posts_url($id)); // Authors
                    }
                    if (!$this->isBlockedUrl($link)) {
                        $xml .= $this->getXml($link, $date);
                    }
                }
            }
        }
        return $xml;
    }
}
