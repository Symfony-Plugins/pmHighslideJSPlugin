<?php

/**
 * This function returns the available languages for translating Highslide JS.
 *
 * @return array
 */
function _get_available_languages()
{
  // en is default language
  return array("en", "es");
}

/**
 * This function obtains a value for specified search string.
 *
 * @param array $options An array of options
 * @param string $key A search string
 *
 * @return string
 */
function _get_highslide_js_option_value($options, $key, $default_value = null)
{
  $value = $default_value;
  if ($key == "lang") {
    if (isset($options[$key])) {
      if (in_array($options[$key], _get_available_languages()))
        $value = $options[$key];
    }
  } else {
    if (isset($options[$key]))
      $value = $options[$key];
  }
  return $value;
}

/**
 * This function adds needed resources in the web response.
 *
 * @param array $options An array of options
 */
function _add_highslide_js_resources($options = array())
{
  $sf_response = sfContext::getInstance()->getResponse();

  // load highslide script
  $sf_response->addJavascript("/pmHighslideJSPlugin/js/highslide.js");

  // load language script
  if ($lang = _get_highslide_js_option_value($options, "lang"))
    $sf_response->addJavascript("/pmHighslideJSPlugin/js/lang/$lang.js");

  // load custom (or default) stylesheet
  if ($css = _get_highslide_js_option_value($options, "css"))
    $sf_response->addStylesheet("$css");
  else
    $sf_response->addStylesheet("/pmHighslideJSPlugin/css/highslide.css");


  // load aditional javascripts
  if ($js = _get_highslide_js_option_value($options, "js")) {
    if (is_array($js))
      foreach ($js as $item)
        $sf_response->addJavascript("$item");
    else
      $sf_response->addJavascript("$js");
  }
}

/**
 * This function calculates highslide's graphics directory path.
 *
 * @return string
 */
function _get_graphics_dir()
{
  $graphics_dir = sfContext::getInstance()->getRequest()->getUriPrefix().sfContext::getInstance()->getRequest()->getRelativeUrlRoot().'/pmHighslideJSPlugin/images/';
  return javascript_tag("hs.graphicsDir = '$graphics_dir';");
}

/**
 * This function gets user specified outline for highslide.
 *
 * @return string
 */
function _get_outline($options)
{
  return isset($options["outline"])?javascript_tag("hs.outline = '{$options["outline"]}';"):null;
}

/**
 * This function returns markup for displaying images in highslide.
 *
 * @param string @image_url The url of the image being displayed
 * @param string @thumb The image, url, or text that represents the image being displayed
 * @param array @options An array of options for Highslide JS
 *
 * @return string
 */
function highslide($image_url, $thumb, $options = array())
{
  _add_highslide_js_resources($options);

  $html = _get_graphics_dir();
  $html .= _get_outline($options);

  $hs = "return hs.expand(this";
  if (isset($options["width"]))
    if (isset($options["height"]) && isset($options["group"]))
      $hs .= ", { width: ".$options["width"].", height: ".$options["height"].", slideshowGroup: '".$options["group"]."'}";
    else if (isset($options["height"]))
      $hs .= ", { width: ".$options["width"].", height: ".$options["height"]."}";
    else if (isset($options["group"]))
      $hs .= ", { width: ".$options["width"].", slideshowGroup: '".$options["group"]."'}";
    else
      $hs .= ", { width: ".$options["width"]."}";
  else if (isset($options["height"]))
    $hs .= ", { height: ".$options["height"]."}";
  else if (isset($options["group"]))
    $hs .= ", { slideshowGroup: '".$options["group"]."'}";
  $hs .= ");";

  $html .= link_to($thumb,
                   $image_url,
                   array("class" => "highslide",
                   "onclick" => $hs));

  if ($heading = _get_highslide_js_option_value($options, "heading"))
    $html .= content_tag("div", __("$heading"), "class=highslide-heading");

  if ($caption = _get_highslide_js_option_value($options, "caption"))
    $html .= content_tag("div", __("$caption"), "class=highslide-caption");

  return $html;
}

/**
 * This function returns markup for displaying html content in highslide.
 *
 * @param string @id The div id
 * @param string @content The html content being displayed
 * @param string @thumb The image, url, or text that represents the html content being displayed
 * @param array @options An array of options for Highslide JS
 *
 * @return string
 */
function highslide_html($id, $content, $thumb, $options = array())
{
  _add_highslide_js_resources($options);

  $html = _get_graphics_dir();
  $html .= _get_outline($options);

  $html .= link_to($thumb,
                  "#",
                  array("class" => "highslide",
                        "onclick" => "return hs.htmlExpand(this, {contentId: '$id'})"));

  if ($style = _get_highslide_js_option_value($options, "style"))
    $html .= tag("div", array("class" => "highslide-html-content", "id" => "$id", "style" => "$style"), true);
  else
    $html .= tag("div", array("class" => "highslide-html-content", "id" => "$id"), true);

  $html .= tag("div", array("class" => "highslide-header"), true);

  $html .= tag("ul", array(), true);
  $html .= content_tag("li", link_to(__("Move"), "#", array("onclick" => "return false")), array("class" => "highslide-move"));
  $html .= content_tag("li", link_to(__("Close"), "#", array("onclick" => "return hs.close(this)")), array("class" => "highslide-close"));
  $html .= tag("/ul", array(), true);

  $html .= tag("/div", array(), true);

  $html .= content_tag("div", __("$content"), array("class" => "highslide-body"));

  $html .= tag("div", array("class" => "highslide-footer"), true);
  $html .= tag("div", array(), true);
  $html .= tag("span", array("class" => "highslide-resize", "title" => __("Resize")), true);
  $html .= tag("span", array(), true);
  $html .= tag("/span", array(), true);
  $html .= tag("/span", array(), true);
  $html .= tag("/div", array(), true);
  $html .= tag("/div", array(), true);
  $html .= tag("/div", array(), true);

  return $html;
}

/**
 * This function returns markup for displaying ajax content in highslide.
 *
 * @param string @url The url of the html content being displayed
 * @param string @thumb The image, url, or text that represents the html content being displayed
 * @param array @options An array of options for Highslide JS
 *
 * @return string
 */
function highslide_ajax($url, $thumb, $options = array())
{
  _add_highslide_js_resources($options);

  $html = _get_graphics_dir();
  $html .= _get_outline($options);

  $html .= link_to("$thumb",
                  "$url",
                  array("onclick" => "return hs.htmlExpand(this, {objectType: 'ajax'})"));

  if ($heading = _get_highslide_js_option_value($options, "heading"))
    $html .= content_tag("div", __("$heading"), "class=highslide-heading");

  if ($caption = _get_highslide_js_option_value($options, "caption"))
    $html .= content_tag("div", __("$caption"), "class=highslide-caption");

  return $html;
}

/**
 * This function returns markup for displaying iframe content in highslide.
 *
 * @param string @url The url of the html content being displayed
 * @param string @thumb The image, url, or text that represents the html content being displayed
 * @param array @options An array of options for Highslide JS
 *
 * @return string
 */
function highslide_iframe($url, $thumb, $options = array())
{
  _add_highslide_js_resources($options);

  $html = _get_graphics_dir();
  $html .= _get_outline($options);

  $html .= link_to("$thumb",
                   "$url",
                   array("onclick" => "return hs.htmlExpand(this, {objectType: 'iframe'})"));

  return $html;
}

/**
 * This function returns markup for displaying flash content in highslide.
 *
 * @param string @url The url of the flash content being displayed
 * @param string @thumb The image, url, or text that represents the flash content being displayed
 * @param array @options An array of options for Highslide JS
 *
 * @return string
 */
function highslide_flash($url, $thumb, $options = array())
{
  _add_highslide_js_resources($options);
  sfContext::getInstance()->getResponse()->addJavascript("http://ajax.googleapis.com/ajax/libs/swfobject/2.1/swfobject.js");

  $width = _get_highslide_js_option_value($options, "width", 300);

  $height = _get_highslide_js_option_value($options, "height", 300);

  $html = _get_graphics_dir();
  $html .= _get_outline($options);

  $html .= link_to("$thumb",
                  "$url",
                  array("class" => "highslide",
                        "onclick" => "return hs.htmlExpand(this, {objectType: 'swf', width: $width, objectWidth: $width, objectHeight: $height, maincontentText: '".__("You need to upgrade your Flash player")."'})"));

  return $html;
}
