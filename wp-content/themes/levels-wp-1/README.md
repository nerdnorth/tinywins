# The Levels Theme: WordPress

Thanks for purchasing the Levels Theme! We think you'll be very happy with your purchase, and we wish you the best in all of your product selling adventures. This document will contain an overview of the Levels Theme for WordPress, and some tips on how to get started.

&nbsp;

---


## Installation

To install place the `levels-theme-wordpress` directory into `wp-content/themes`. For more details, read about [installing themes on the WordPress Codex](https://codex.wordpress.org/Using_Themes).

&nbsp;

## Shortcode Reference

When using Levels in WordPress you can take advantage of several shortcodes to simplify the page creation process.

PS. If you're not familiar with shortcodes, [checkout this page on the WordPress Codex](https://codex.wordpress.org/Shortcode).

### List of Shortcodes

1. [Masthead - `[lmasthead]`](#lmasthead)
2. [Quote - `[lquote]`](#lquote)
3. [Highlight - `[lighlight]`](#lhighlight)
3. [Checkbox List - `[lcheckboxlist]`](#lcheckboxlist)
4. [Book SVG - `[lbook]`](#lbook)
5. [Callout Box - `[lcallout]`](#lcallout)
6. [Pull - `[lpull]`](#lpull)
7. [Table of Contents - `[ltoc]`](#ltoc)
8. Packages
   - [Package Section - `[lpackagesection]`](#lpackagesection)
   - [Package - `[lpackage]`](#lpackage)
   - [Package Feature - `[lpackagefeature]`](#lpackagefeature)
9. [Author Block - `[lauthor]`](#lauthor)

### Example Source Code

Check out this page for example source code of a page in WordPress using all these shortcodes; [https://gist.github.com/hamstu/0936b54a9dd25fbb705b](https://gist.github.com/hamstu/0936b54a9dd25fbb705b)

&nbsp;

---

<a name="lmasthead"></a>
### Masthead (`[lmasthead]`)

The masthead is the large text and tagline generally placed at the top of the Levels Theme page.

    [lmasthead title="<TITLE>" tagline="<TAGLINE>" extra_margin="<MARGIN>"]

**Attributes:**

* `title`: Text for the masthead.
* `tagline`: Tagline for the masthead.
* `extra_margin`: (Optional) Extra margin to apply to the `top` and `bottom` of the masthead. Can be any valid CSS value unit (20px, 2em, etc.).

<a name="lquote"></a>
### Quote (`[lquote]`)

A large blockquote. Make your quotes big and awesome!
(Looks best with shorter text.)

    [lquote source="<SOURCE>" source_link="<URL>"]<TEXT>[/lquote]


**Attributes:**

* `source`: Text for the masthead.
* `source_link`: Tagline for the masthead.

<a name="lhighlight"></a>
### Highlight (`[lhighlight]`)

Highlight text with a light-yellow background for that extra bit of emphasis.

    [lhighlight]<TEXT>[/lhighlight]

<a name="lcheckboxlist"></a>
### Checkbox List (`[lcheckboxlist]`)

Make your lists look even cooler by adding nifty checkbox icons.


    [lcheckboxlist]
    <ul>
      <li>Chrono Trigger</li>
      <li>Final Fantasy VII</li>
      <li>Undertale</li>
    </ul>
    [/lcheckboxlist]

<a name="lbook"></a>
### Book (SVG Graphic) (`[lbook]`)

The book SVG graphic is a customizable graphic that inherits the color of your theme, you can also change the text that shows on the cover.

    [lbook byline="<BYLINE>" align="<ALIGN>"]<TITLE>[/lbook]

**Attributes:**

* `byline`: The byline on the cover.
* `align`: Alignment of book; can be `left` or `right`.

<a name="lcallout"></a>
### Callout Box (`[lcallout]`)

Wrap your content in a pretty box.

    [lcallout header="<HEADER>"]
        <CONTENT>
    [/lcallout]

**Attributes:**

* `header`: Header title for the callout box. Make it something clever.
* **Inner Content:** The contents of the callout box. Can inclue other shortcodes.

<a name="lpull"></a>
### Content Pull (`[lpull]`)

Pull the content to one side or the other.

    [lpull to="<DIRECTION>"]
      <CONTENT>
    [/lpull]

**Attributes:**

* `to`: Direction of pull; can be `left` or `right`.
* **Inner Content:** The contents of the pull. Can inclue other shortcodes.

<a name="ltoc"></a>
### Table of Contents (`[ltoc]`)

You can use this shortcode to generate a pretty table of contents for your page.

    [ltoc title="<TITLE>"]
      * Section Title | 2
      - Chapter Title | 2
      - Another Chapter | 4
      * Another Section Title | 5
      - The Third Chapter | 6
    [/ltoc]

**Attributes:**

* `title`: Title text at the top of the table of contents. (Which, you know, you would probably set to `"Table of Contents"`)
* **Inner Content:** The table of contents, specially formatted. (See below.)

**How To Use:**

As shown in the example, you must specifically format each line to designate how it appears. The format is:

    [* or -] [TEXT] | [PAGE NUMBER]

If you start the line with a star (`*`) then it will appear as a section in the ToC, whereas the dash (`-`) designates a normal chapter. Play around and you'll see how it works.

<a name="lpackagesection"></a>
### Packages Section (`[lpackagesection]`)

This section is the container around your various package blocks. It's important that you include this, or things won't look quite right. It sets up that nice dark background.

    [lpackagesection title="Get The Title of Your Book" subtitle="Choose the package that works for you."]

    [...] (Other package shortcodes go here)

    [/lpackagesection]

**Attributes:**

* `title`: The title of your package section.
* `subtitle`: The subtitle of your package section.
* **Inner Content:** `[lpackage]` shortcodes.

**How To Use:**

This is just a wrapper/container. Keep reading to learn what shortcodes to use _inside_ the packages section to bring it all together.

<a name="lpackage"></a>
### Package (`[lpackage]`)

This shortcode is for a single package on your page. Be sure to insert these only within a `[lpackagesection]` container.

    [lpackage id="package-complete" price="$75" discounted_price="$100" btn_text="Buy Now" btn_href="http://google.com" btn_class="button--bright" name="The Complete Package" description="This package has it all. You won't miss a thing."]

    [...]

    [/lpackage]

**Attributes:**

* `id`: (Optional) This is the same as the `id` attribute in HTML. It's optional. You might use it so you can link directly to certain packages on the page.
* `price`: The current price of your package.
* `discounted_price`: The old/original price of your package (it will be crossed out <s>like this</s>).
* `btn_text`: Text on your button, e.g., `"Buy Now"`.
* `btn_href`: The `href` or link when the button is clicked.
* `btn_class`: (Optional) Custom CSS class for the button. The built in `button--bright` will make your button orange.
* `name`: The name of this package.
* `description`: A short description of the package.
* **Inner Content:** `[lpackagefeature]` shortcodes.

**How To Use:**

This shortcode serves as both a representation of your package, and as a wrapper for the related `[lpackagefeature]` shortcodes described next.

<a name="lpackagefeature"></a>
### Package Feature (`[lpackagefeature]`)

A package feature item. Must be the child of an `[lpackage]` shortcode.

    [lpackagefeature name="The Full eBook" description="300 pages." expand_id="expand-ebook-info" icon_class="fa fa-book"]

**Attributes:**

* `name`: The name of this feature.
* `description`: Very short description of the feature.
* `expand_id`: (Optional) If specified, the `<div>` matching this id will be shown when the feature is clicked. (See How To Use below.)
* `icon_class`: Class for the icon. FontAwesome is included by default, so you can use any of their icon class names; [fortawesome.github.io/Font-Awesome/icons/](fortawesome.github.io/Font-Awesome/icons/)

**How To Use:**

This shortcode is fairly simple. To make sure the example above works when clicked (because it has an `expand_id` attribute), you would creae a `<div>` like this:

    <div id="expand-ebook-info" style="display:none;">This is more info on the eBook.</div>

Place that code anywhere in your HTML. If you want to be neat and tidy, keep any other expand divs in the same lace.

Now clicking the feature should then show the content in that div.

<a name="lauthor"></a>
### Author Block

Shows a big and pretty author section.

    [lauthor image_src="<URL>"]

    <TEXT>

    [/lauthor]

**Attributes:**

* `image_src`: URL to an image of your beautiful face, or anything really. Could even be a cat GIF.
* **Inner Content:** Whatever you have to say about yourself.


## WordPress Theme Options

Once Levels Theme is installed and activated in WordPress you can access the Settings page by clicking the "Levels Theme" link in your WordPress admin. There you can tweak a few simple options.

## Customizing Colors

To customize the colors of your theme, head over to the [Levels Theme Customizer](http://levelstheme.com/customize). Be sure to check the box that says "[ ] WordPress". Download and save the new `style.css` over the one that came with the theme.

## Help and Support

This theme is brought to you by [@hamstu](https://twitter.com/hamstu) and [@mijustin](https://twitter.com/mijustin). Hit us up on twitter ([@levelstheme](https://twitter.com/levelstheme)) if you need any help! You can also reach us at: [levelstheme@gmail.com](mailto:levelstheme@gmail.com).

