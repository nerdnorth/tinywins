<?php

/**
 * Custom shortcodes for the Levels Theme.
 *
 * @package levels
 */


/**
 * Page Masthead
 *
 * @param   array $attributes
 * @param   string $content
 * @return  string (HTML)
 */
function levelsMasthead( $attributes, $content ){

  $attributes = (object) shortcode_atts( array(
    'title' => '',
    'tagline' => '',
    'extra_margin' => 0
  ), $attributes );

  $style = "";

  if ($attributes->extra_margin) {

    $style = "margin: {$attributes->extra_margin} 0 {$attributes->extra_margin} 0;";

  }

  $ret =
    "<header class='header header--wp header--centered' style='{$style}'>" .
      "<h1 class='header__title'>{$attributes->title}</h1>" .
      "<p class='header__tagline'>{$attributes->tagline}</p>" .
    "</header>";

  return $ret;

}
add_shortcode( 'lmasthead', 'levelsMasthead' );

/**
 * Styled Blockquote
 *
 * @param array $attributes
 * @param string $content
 * @return string (HTML)
 */
function levelsQuote( $attributes, $content ){

  $attributes = (object) shortcode_atts( array(
    'source' => null,
    'source_link' => null,
  ), $attributes );

  $ret =
    "<div class='break'>" .
    "<blockquote class='quote'>" .
    "  <div class='container container--widened'>" .
    "     <p class='quote__text'>" . $content . "</p>";

  if ($attributes->source) {

    if ($attributes->source_link) {

      $ret .= "<cite class='quote__byline'><a href='{$attributes->source_link}'>{$attributes->source}</a></cite>";

    } else {

      $ret .= "<cite class='quote__byline'>{$attributes->source}</cite>";

      }

  }

  $ret .=
    '  </div>' .
    '</blockquote>' .
    '</div>';

  return $ret;

}
add_shortcode( 'lquote', 'levelsQuote' );

/**
 * Highlight text
 *
 * @param   array $attributes
 * @param   string $content
 * @return  string (HTML)
 */
function levelsHighlight( $attributes, $content ){

  return "<em class='u-highlight'>{$content}</em>";

}
add_shortcode( 'lhighlight', 'levelsHighlight' );

/**
 * Checkbox List
 *
 * @param   array $attributes
 * @param   string $content
 * @return  string (HTML)
 */
function levelsCheckboxList( $attributes, $content ){

  $dom = new DOMDocument();
  @$dom->loadHTML($content);
  $xpath = new DOMXPath($dom);

  foreach($xpath->query("//ul|//ol|//li") as $node)
  {
    $currentClass = $node->getAttribute("class");
    $newClass     = $node->tagName == 'li' ? 'checkbox-list__item' : 'checkbox-list';
    $newClassList = $currentClass . $newClass;
    $node->setAttribute("class", $newClassList);
  }

  $content = $dom->saveHtml();

  return $content;

}
add_shortcode( 'lcheckboxlist', 'levelsCheckboxList' );

/**
 * Book
 *
 * @param   array $attributes
 * @param   string $content
 * @return  string (HTML)
 */
function levelsBook( $attributes, $content ){

  static $includedSvg = false;

  $attributes = (object) shortcode_atts( array(
    'byline' => null,
    'align' => 'none'
  ), $attributes );

  $ret = "";

  if (!$includedSvg) {
    $svg = file_get_contents( get_template_directory() . '/inc/book-svg.html' );
    $ret .= $svg . "\n\n";
    $includedSvg = true;
  }

  $ret .=
    "<div class='book-wrapper book-wrapper--align-{$attributes->align}'>" .
    "  <svg class='book-wrapper__svg'>" .
    "    <use xlink:href='#book'>" .
    "  </svg>" .
    "  <div class='book-wrapper__title'>" . $content;

  if  ($attributes->byline) {

    $ret .= "<div class='book-wrapper__byline'>{$attributes->byline}</div>";

  }

  $ret .=
    "  </div>" .
    "</div>";

  return $ret;

}
add_shortcode( 'lbook', 'levelsBook' );

/**
 * Callout Box
 *
 * @param   array $attributes
 * @param   string $content
 * @return  string (HTML)
 */
function levelsCallout( $attributes, $content ){

  $attributes = (object) shortcode_atts( array(
    'header' => '',
    'header_tag' => 'h2'
  ), $attributes );

  if ( !empty($attributes->header) ) {
    $attributes->header = "<{$attributes->header_tag} class='section-heading--with-line'>{$attributes->header}</{$attributes->header_tag}>";
  }

  $ret =
    "<div class='callout-box callout-box--spaced'>" .
      $attributes->header .
      wpautop(do_shortcode($content)) .
    "</div>";

  return $ret;

}
add_shortcode( 'lcallout', 'levelsCallout' );

/**
 * Pull
 *
 * @param   array $attributes
 * @param   string $content
 * @return  string (HTML)
 */
function levelsPull( $attributes, $content ){

  $attributes = (object) shortcode_atts( array(
    'to' => 'left'
  ), $attributes );

  $ret =
  "<div class='body-copy-{$attributes->to}'>" .
    wpautop(do_shortcode($content)) .
  "</div>";

  return $ret;

}
add_shortcode( 'lpull', 'levelsPull' );

/**
 * Table of Contents
 *
 * @param   array $attributes
 * @param   string $content
 * @return  string (HTML)
 */
function levelsToc( $attributes, $content ){

  $attributes = (object) shortcode_atts( array(
    'title' => 'Table of Contents'
  ), $attributes );

  $lines = preg_split ('/$\R?^/m', $content);

  $templateStart =
    "<div class='clear'></div><div class='toc'>" .
    "  <h2 class='section-heading section-heading--with-line'>{$attributes->title}</h2>" .
    "  <div class='toc__bookmark'></div>" .
    "    <ul class='toc__list'>";

  $templateEnd = "</div></div>";

  $templateBody = "";

  $groupStart   = "<div class='toc__group'>";
  $groupEnd     = "</div>";
  $prevGroup    = false;

  foreach ($lines as $line) {

    $line = trim(strip_tags($line));

    preg_match("/^([\*-])\s*(.*?)\s*(\|\s*(\d+))*$/i", $line, $matches);

    $lineHTML = "";

    if ($matches) {

      $chapterSection = isset($matches[1]) ? ( $matches[1] == '*' ) : false;
      $className      = $chapterSection ? 'toc__item--section' : 'toc__item toc__item--indented';
      $text           = isset($matches[2]) ? wptexturize($matches[2]) : '';
      $pageNumber     = isset($matches[4]) ? $matches[4] : '';

      $lineHTML =
        "<li class='{$className}'>" .
          "<span>{$pageNumber}</span>" .
          "<span>{$text}</span>" .
        "</li>";

      if ($chapterSection) {

        $lineHTML = ($prevGroup ? $groupEnd : '') . $groupStart . $lineHTML;
        $prevGroup = true;

      }

    }

    $templateBody .= $lineHTML;

  }

  if ($prevGroup) {

    $templateBody .= $groupEnd;

  }

  return $templateStart . $templateBody . $templateEnd;

}
add_shortcode( 'ltoc', 'levelsToc' );

/**
 * Package Section
 *
 * @param   array $attributes
 * @param   string $content
 * @return  string (HTML)
 */
function levelsPackageSection( $attributes, $content ){

  $attributes = (object) shortcode_atts( array(
    'title' => null,
    'subtitle' => null
  ), $attributes );

  $ret =
  "<div class='break'>" .
  "<div class='dark'>" .
  "  <div class='container container--normal'>" .
  "    <h2 class='packages__title'>{$attributes->title}</h2>" .
  "    <p class='packages__subtitle'>{$attributes->subtitle}</p>" .

       do_shortcode($content) .

  "  </div>" .
  "</div>" .
  "</div>";

  return $ret;

}
add_shortcode( 'lpackagesection', 'levelsPackageSection' );

/**
 * Package
 *
 * @param   array $attributes
 * @param   string $content
 * @return  string (HTML)
 */
function levelsPackage( $attributes, $content ){

  $attributes = (object) shortcode_atts( array(
    'id'                  => null,
    'price'               => null,
    'discounted_price'    => null,
    'btn_text'            => 'Buy Now',
    'btn_href'            => '#',
    'btn_class'           => '',
    'name'                => null,
    'description'         => null,

  ), $attributes );

  if ($attributes->discounted_price) {
    $price = "<s>{$attributes->discounted_price}</s> {$attributes->price}";
  } else {
    $price = $attributes->price;
  }

  $ret =
  "<div class='package' id='{$attributes->id}'>" .

    "<a href='{$attributes->btn_href}' class='button package__button {$attributes->btn_class}'>" .
      "<span class='package__button-price'>{$price}</span>" .
      "<span class='package__button-text'>{$attributes->btn_text}</span>" .
    "</a>" .

    "<h3 class='package__name'>{$attributes->name}</h3>" .
    "<p class='package__description'>{$attributes->description}</p>" .

    "<div class='package__features-list'>" .

      do_shortcode($content) .

    "</div>" .

  "</div>";

  return $ret;

}
add_shortcode( 'lpackage', 'levelsPackage' );

/**
 * Package
 *
 * @param   array $attributes
 * @param   string $content
 * @return  string (HTML)
 */
function levelsPackageFeature( $attributes ){

  $attributes = (object) shortcode_atts( array(
    'expand_id' => null,
    'icon_class' => 'fa fa-check',
    'name'        => 'Feature Name',
    'description' => 'Feature Description',
    'description_prefix' => ' &mdash; '
  ), $attributes );

  if ($attributes->expand_id) {

    $tag = "a href='#'";
    $className = "package__feature-item--expandable js-expand";

  } else {

    $tag = "div";
    $className = "";

  }

  $ret =
  "<$tag class='package__feature-item {$className}' data-expand-id='{$attributes->expand_id}'>" .
    "<span class='package__feature-icon'><i class='{$attributes->icon_class}'></i></span>" .
    "<span class='package__feature-title'>{$attributes->name}</span>" .
    "<span class='package__feature-info'>{$attributes->description_prefix}{$attributes->description}</span>" .
  "</$tag>";

  return $ret;

}
add_shortcode( 'lpackagefeature', 'levelsPackageFeature' );

/**
 * Package Expand
 *
 * @param   array $attributes
 * @param   string $content
 * @return  string (HTML)
 */
function levelsPackageExpand( $attributes, $content ){

  $attributes = (object) shortcode_atts( array(
    'id' => '',
    'image_src' => null
  ), $attributes );

  $innerContent = wpautop(do_shortcode($content));

  if ($attributes->image_src) {

    $innerContent =
    "<div class='package__feature-details-left'>" .
      "<img src='{$attributes->image_src}' class='package__feature-details-img'>" .
    "</div>" .

    "<div class='package__feature-details-right'>" .
        $innerContent .
    "</div>";

  }

  $ret =
    "<div id='{$attributes->id}' class='package__feature-details'>" .
    "<div class='container container--normal'>" .

      $innerContent .

    "</div>" .
    "</div>";


  return $ret;

}
add_shortcode( 'lpackageexpand', 'levelsPackageExpand' );

/**
 * Author
 *
 * @param   array $attributes
 * @param   string $content
 * @return  string (HTML)
 */
function levelsAuthor( $attributes, $content ){

  $attributes = (object) shortcode_atts( array(
    'id' => '',
    'image_src' => null
  ), $attributes );

  $innerContent = wpautop(do_shortcode($content));

  if ($attributes->image_src) {

    $innerContent =
    "<div class='author__left'>" .
      "<img src='{$attributes->image_src}' class='author__image'>" .
    "</div>" .

    "<div class='author__right'>" .
        $innerContent .
    "</div>";

  }

  $ret =
    "<div class='break'>" .
    "<div class='author__container'>" .
    "<div class='container container--grid'>" .

      $innerContent .

    "</div>" .
    "</div>" .
    "</div>";


  return $ret;

}
add_shortcode( 'lauthor', 'levelsAuthor' );

/**
 * Exempt some of our shortcodes from wptexturize
 */
add_filter( 'no_texturize_shortcodes', 'shortcodes_to_exempt_from_wptexturize' );

function shortcodes_to_exempt_from_wptexturize( $shortcodes ) {
    $shortcodes = array_merge(array('ltoc'), $shortcodes);
    return $shortcodes;
}

/**
 * Filter out newlines and such from certain shortcodes
 * http://wordpress.stackexchange.com/questions/130075/stop-wordpress-automatically-adding-br-tags-to-post-content
 */
function levels_content_filter($content) {
  $block = join("|", array("ltoc", "lpackage", "lpackagesection", "lpackagefeature", "lcheckboxlist"));
  $rep = preg_replace("/(<p>)?\[($block)(\s[^\]]+)?\](<\/p>|<br \/>)?/","[$2$3]",$content);
  $rep = preg_replace("/(<p>)?\[\/($block)](<\/p>|<br \/>)?/","[/$2]",$rep);
  $rep = str_replace( array( '<p></p>' ), '', $rep );
  $rep = str_replace( array( '<p>  </p>' ), '', $rep );
  error_log($rep);
  return $rep;
}
//remove_filter( 'the_content', 'wpautop' );
//add_filter( 'the_content', 'wpautop' , 99);
add_filter( 'the_content', 'levels_content_filter' );

