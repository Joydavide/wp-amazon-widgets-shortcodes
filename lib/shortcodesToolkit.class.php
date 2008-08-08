<?php
/**
 * Tookit functions used by the AmazonWidgetsShortcodesTags class
 * 
 * @author oncletom
 * @version 1.0
 * @since 1.0 alpha 2
 */
class AmazonWidgetsShortcodesToolkit
{
  /**
   * Displays context links on Amazon links
   * 
   * @author oncletom
   * @version 1.1
   * @since 1.0 alpha 3
   * @return 
   */
  function displayContextLinks()
  {
    $tracking_id = get_option('awshortcode_tracking_id');
    $region = $this->getRegionParameters();
    $src = $region['url']['tool-contextlinks'];
    echo <<<EOF
<script type="text/javascript">//<![CDATA[
var amzn_cl_tag = '{$tracking_id}';
//]]></script><script type="text/javascript" src="{$src}"></script>
EOF;
  }

  /**
   * Displays context links on Amazon links
   * 
   * @author oncletom
   * @version 1.1
   * @since 1.0 alpha 3
   * @return 
   */
  function displayProductPreview()
  {
    $region = $this->getRegionParameters();
    $src = sprintf(
            $region['url']['tool-productpreview'],
            get_option('awshortcode_tracking_id')
           );

    echo <<<EOF
<script type="text/javascript" src="{$src}"></script>
EOF;
  }

  /**
   * Displays or not the widget according to some conditions
   * 
   * Conditions are:
   * - publication to feed is enabled (disabled by default)
   * - AND we are in a feed
   * 
   * @author oncletom
   * @version 1.0
   * @since 1.0 alpha 2
   * @return $html String HTML widget
   * @param $html String HTML widget
   */
  function displayShortcode($html)
  {
    return get_option('awshortcode_feed') || !is_feed() ? $html : '';
  }

  /**
   * Encode some parameters located in the query string of an URL for proper behavior purpose
   * 
   * Encode characters such as :
   * - / -> %2F
   * - & -> &amp;
   * - &amp;amp; -> &amp; (avoids double "&" encoding)
   * 
   * @author oncletom
   * @version 1.0
   * @since 1.1 beta
   * @return $encoded_uri String Encoded URI
   * @param $uri String Uri to encode
   */
  function encodeParameters($uri)
  {
    $parsed_uri = parse_url($uri);
    $qs_original = $parsed_uri['query'];
    $qs = str_replace(array('/', '&', '&amp;amp;'), array('%2F', '&amp;', '&amp;'), $qs_original);

    return str_replace($qs_original, $qs, $uri);
  }

  /**
   * Loads additional buttons into the TinyMCE 3 UI
   * 
   * @author oncletom
   * @version 1.0
   * @since 1.1
   * @return $buttons Array Set of TinyMCE buttons, modified
   * @param $buttons Array Set of TinyMCE buttons
   */
  function executeTinymce3Buttons($buttons)
  {
    $buttons[] = 'awshortcode-selector';
    return $buttons;
  }

  /**
   * Execute TinyMCE 3 hooks and filters
   * 
   * @author oncletom
   * @version 1.0
   * @since 1.1
   * @return null or false if no permission to edit page or post
   */
  function executeTinymce3Hook()
  {
    if (!current_user_can('edit_posts') && !current_user_can('edit_pages'))
    {
      return false;
    }

    if (get_user_option('rich_editing') == 'true')
    {
      add_filter('mce_external_plugins', array('AmazonWidgetsShortcodesToolkit', 'executeTinymce3Plugins'));
      add_filter('mce_buttons', array('AmazonWidgetsShortcodesToolkit', 'executeTinymce3Buttons'));
      add_filter('mce_external_languages', array('AmazonWidgetsShortcodesToolkit', 'executeTinymce3Langs') );
    }
  }

  /**
   * Loads TinyMCE 3 language files
   * 
   * @author oncletom
   * @version 1.0
   * @since 1.1
   * @return $langs Array Set of TinyMCE languages, modified
   * @param $langs Array Set of TinyMCE languages
   */
  function executeTinymce3Langs($langs)
  {
    $langs['wpAwshortcode'] = WP_PLUGIN_DIR.'/amazon-widgets-shortcodes/web/javascript/tinymce3/wpAwshortcode/langs/langs.php';
    return $langs;
  }

  /**
   * Loads TinyMCE 3 external plugins
   * 
   * @author oncletom
   * @version 1.0
   * @since 1.1
   * @return $plugins Array Set of TinyMCE plugins, modified
   * @param $plugins Array Set of TinyMCE plugins
   */
  function executeTinymce3Plugins($plugins)
  {
    $plugins['wpAwshortcode'] = WP_PLUGIN_URL.'/amazon-widgets-shortcodes/web/javascript/tinymce3/wpAwshortcode/editor_plugin.js';
    return $plugins;
  }

  /**
   * Wraps content in Amazon proprietary HTML comments tag
   * 
   * Context links are added by Amazon engine only between those parts.
   * 
   * @author oncletom
   * @version 1.0
   * @since 1.0 beta 1
   * @return $html String Filtered HTML
   * @param $html String Post/page content to filter
   */
  function filterContextLinks($html)
  {
    return
      '<!--Amazon_CLS_IM_START-->'.
      $html.
      '<!--Amazon_CLS_IM_END-->';
  }

  /**
   * Removes nasty tag wrapping the shortcode
   * 
   * @author oncletom
   * @version 1.0
   * @since 1.0 beta 2
   * @return $html String Filtered HTML
   * @param $html String Post/page content to filter
   */
  function filterXhtmlFormatting($html)
  {
    return preg_replace(
             '#<p>(<div .+ class="awshortcode-[a-z0-9]+">.+</div>)</p>#sU',
             "$1",
             $html
           );
  }

  /**
   * Getter for international Amazon parameters
   * 
   * @author oncletom
   * @version 1.2
   * @since 1.0 beta 1
   * @return $settings Array Settings for all or a limited area
   * @param $country_code String[Optionnal] limit the returned settings to this country code
   */
  function getRegionParameters($country_code = 'autodetect')
  {
    if ($country_code == 'autodetect')
    {
      $country_code = get_option('awshortcode_region');
      $country_code = $country_code ? $country_code : 'us';
    }

    $amazon = array(
      'ca' => array(
        'lang_iso_code' => 'en_CA',
        'marketplace' => 'CA',
        'name' => __('Amazon Canada', 'awshortcode'),
        'suffix' => '-20',
        'url' => array(
          'affiliate' => 'http://associates.amazon.ca/',
          'site' => 'http://www.amazon.ca/',
          'tool-contextlinks' => 'http://cls.assoc-amazon.ca/ca/s/cls.js',
          'tool-productpreview' => 'http://www.assoc-amazon.ca/s/link-enhancer?tag=%s&amp;o=15',
          'widget-carrousel' => 'http://ws.amazon.ca/widgets/q?ServiceVersion=20070822&amp;MarketPlace=%s&amp;ID=V20070822%%2F%1$s%%2F%s%%2F8010%%2F%s&amp;Operation=%s',
          'widget-product' => 'http://rcm-ca.amazon.ca/e/cm?t=%s&amp;o=15&amp;p=8&amp;l=as1&amp;asins=%s&amp;fc1=%s&amp;%s=1&amp;lt1=%s&amp;lc1=%s&amp;bc1=%s&amp;bg1=%s&amp;f=ifr',
          'widget-slideshow' => 'http://ws.amazon.ca/widgets/q?ServiceVersion=20070822&amp;MarketPlace=%s&amp;ID=V20070822%%2F%1$s%%2F%s%%2F8003%%2F%s&amp;Operation=%s',
          'widget-wishlist' =>  'http://ws.amazon.ca/widgets/q?ServiceVersion=20070822&amp;MarketPlace=%s&amp;ID=V20070822/%1$s/%s/8004/%s',
        ),
      ),
      /*'de' => array(
        'lang_iso_code' => 'de_DE',
        'marketplace' => 'DE',
        'name' => __('Amazon Germany', 'awshortcode'),
        'suffix' => '-21',
        'url' => array(
          'affiliate' => '',
          'site' => 'http://www.amazon.de/',
          'tool-contextlinks' => '',
          'tool-productpreview' => '',
          'widget-carrousel' => '',
          'widget-product' => '',
        ),
      ),*/
      'fr' => array(
        'lang_iso_code' => 'fr_FR',
        'marketplace' => 'FR',
        'name' => __('Amazon France', 'awshortcode'),
        'suffix' => '-21',
        'url' => array(
          'affiliate' => 'http://partenaires.amazon.fr/',
          'site' => 'http://www.amazon.fr/',
          'tool-contextlinks' => 'http://cls.assoc-amazon.fr/fr/s/cls.js',
          'tool-productpreview' => 'http://www.assoc-amazon.fr/s/link-enhancer?tag=%s&o=8',
          'widget-carrousel' => 'http://ws.amazon.fr/widgets/q?ServiceVersion=20070822&amp;MarketPlace=%s&amp;ID=V20070822%%2F%1$s%%2F%s%%2F8010%%2F%s&amp;Operation=%s',
          'widget-product' => 'http://rcm-fr.amazon.fr/e/cm?t=%s&amp;o=8&amp;p=8&amp;l=as1&amp;asins=%s&amp;fc1=%s&amp;%s=1&amp;lt1=%s&amp;lc1=%s&amp;bc1=%s&amp;bg1=%s&amp;f=ifr',
          'widget-slideshow' => 'http://ws.amazon.fr/widgets/q?ServiceVersion=20070822&amp;MarketPlace=%s&amp;ID=V20070822%%2F%1$s%%2F%s%%2F8003%%2F%s&amp;Operation=%s',
          'widget-wishlist' =>  'http://ws.amazon.fr/widgets/q?ServiceVersion=20070822&amp;MarketPlace=%s&amp;ID=V20070822/%1$s/%s/8004/%s',
        ),
      ),
      /*'jp' => array(
        'lang_iso_code' => 'ja_JP',
        'marketplace' => 'JP',
        'name' => __('Amazon Japan', 'awshortcode'),
        'suffix' => '',
        'url' => array(
          'affiliate' => '',
          'site' => 'http://www.amazon.co.jp/',
          'tool-contextlinks' => '',
          'tool-productpreview' => '',
          'widget-carrousel' => '',
          'widget-product' => '',
        ),
      ),*/
      'uk' => array(
        'lang_iso_code' => 'en_UK',
        'marketplace' => 'UK',
        'name' => __('Amazon United Kingdom', 'awshortcode'),
        'suffix' => '-21',
        'url' => array(
          'affiliate' => 'http://affiliate-program.amazon.co.uk/',
          'site' => 'http://www.amazon.co.uk/',
          'tool-contextlinks' => 'http://cls.assoc-amazon.co.uk/gb/s/cls.js',
          'tool-productpreview' => 'http://www.assoc-amazon.co.uk/s/link-enhancer?tag=%s&amp;o=2',
          'widget-carrousel' => 'http://ws.amazon.co.uk/widgets/q?ServiceVersion=20070822&amp;MarketPlace=%s&amp;ID=V20070822%%2F%1$s%%2F%s%%2F8010%%2F%s&amp;Operation=%s',
          'widget-product' => 'http://rcm-uk.amazon.co.uk/e/cm?t=%s&amp;o=2&amp;p=8&amp;l=as1&amp;asins=%s&amp;fc1=%s&amp;%s=1&amp;lt1=%s&amp;lc1=%s&amp;bc1=%s&amp;bg1=%s&amp;f=ifr',
          'widget-slideshow' => 'http://ws.amazon.co.uk/widgets/q?ServiceVersion=20070822&amp;MarketPlace=%s&amp;ID=V20070822%%2F%1$s%%2F%s%%2F8003%%2F%s&amp;Operation=%s',
          'widget-wishlist' =>  'http://ws.amazon.co.uk/widgets/q?ServiceVersion=20070822&amp;MarketPlace=%s&amp;ID=V20070822/%1$s/%s/8004/%s',
        ),
      ),
      'us' => array(
        'lang_iso_code' => 'en_US',
        'marketplace' => 'US',
        'name' => __('Amazon USA', 'awshortcode'),
        'suffix' => '-20',
        'url' => array(
          'affiliate' => 'https://affiliate-program.amazon.com/',
          'site' => 'http://www.amazon.com/',
          'tool-contextlinks' => 'http://cls.assoc-amazon.com/s/cls.js',
          'tool-productpreview' => 'http://www.assoc-amazon.com/s/link-enhancer?tag=%s&amp;o=1',
          'widget-carrousel' => 'http://ws.amazon.com/widgets/q?ServiceVersion=20070822&amp;MarketPlace=%s&amp;ID=V20070822%%2F%1$s%%2F%s%%2F8010%%2F%s&amp;Operation=%s',
          'widget-product' => 'http://rcm.amazon.com/e/cm?t=%s&amp;o=1&amp;p=8&amp;l=as1&amp;asins=%s&amp;fc1=%s&amp;%s=1&amp;lt1=%s&amp;lc1=%s&amp;bc1=%s&amp;bg1=%s&amp;f=ifr',
          'widget-slideshow' => 'http://ws.amazon.com/widgets/q?ServiceVersion=20070822&amp;MarketPlace=%s&amp;ID=V20070822%%2F%1$s%%2F%s%%2F8003%%2F%s&amp;Operation=%s',
          'widget-wishlist' =>  'http://ws.amazon.com/widgets/q?ServiceVersion=20070822&amp;MarketPlace=%s&amp;ID=V20070822/%1$s/%s/8004/%s',
        ),
      ),
    );

    return $country_code ? $amazon[$country_code] : $amazon;
  }
}
?>