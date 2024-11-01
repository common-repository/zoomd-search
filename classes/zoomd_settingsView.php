<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
use Zoomd\Core\Activator;
use Zoomd\Core\Settings; 

class zoomd_settingsView {
	
    public static function show_settings() {    
    //delete_option('zoomd_emailaddress');
    $clientId  = Settings::clientId();
    $emailaddress  = Settings::email();
    $adminUrl = "http://www.zoomd.com";
    
    $replacesearchbox = Settings::replacesearchbox();
    $showfloatingicon = Settings::showfloatingicon();
    $searchboxhtml = Settings::searchboxhtml(); 
    $enabletopsearches = Settings::enabletopsearches(); 
    $floatingicontop = Settings::floatingicontop(); 
    $floatingiconright = Settings::floatingiconright(); 


    
    delete_option('zoomd_notices');
    $style = 'style="width: 250px;"';

    
 ?>   
<form method="post" action="options.php"> 
    <?php settings_fields('zoomd_search_options'); ?>    
    <div class="zdpage">

        <!--Header-->
        <div>
            <!--<h1>Zoomd Settings</h1>-->
            <img align="right" src="http://zoomd.com/wp-content/uploads/2015/12/logo.png" alt="Zoomd" >
            <!--</br></br></br></br></br></br>-->
            <div >
                <div>
                    <!--<p style="color:navy" ><?php _e( 'Your site is being indexed', 'zoomd-search' );?></p>-->
                    <p><?php _e( 'Thanks you for creating a Zoomd Search account!, For feedback/support please contact <a href="mailto:support@zoomd.com">support@zoomd.com', 'zoomd-search' );?></a></p>
                </div>
            </div>
        </div>        
        <div id="accordion" >
            <!--Account Info-->
            <h4 class="zdaccordion-toggle"><?php _e( 'Zoomd Account', 'zoomd-search' );?></h4>
            <div class="zdaccordion-content default">            
                <table width="100%" cellpadding="0">
                <tr>
                    <td class="zdtdlable">
                        <label><?php _e( 'Your Zoomd ClientID', 'zoomd-search' );?>: </label>
                    </td>
                    <td>
                        <span><?php echo $clientId ?></span>
                    </td>
                </tr>                
            </table>
            </div>

            <!--Searchbox Settings-->
            <h4 class="zdaccordion-toggle"><?php _e( 'Searchbox Settings', 'zoomd-search' );?></h4>
            <p style="padding-left:10px;font-style: italic;color:seagreen"><?php _e( 'In order to implement your own search trigger, just add the following attribute to the triggering element:', 'zoomd-search' );?><code> zoomdsearch="{ "trigger" : "OnClick" }" </code></p>
            <div class="zdaccordion-content default">                                                
                <p>
                        <input type="checkbox" name="zoomd_options[showfloatingicon]" id="zoomd_showfloatingicon" <?php checked('1', $showfloatingicon); ?>
                            value="1"  /><?php _e( 'Add floating search icon', 'zoomd-search' );?>
                        <span>Floating icon Location, Top:</span>
                        <input type="text" class="zdfloatingtext" id="zoomd_floatingicontop" name="zoomd_options[floatingicontop]" value="<?php echo $floatingicontop;?>" size="2"/>
                        <span>Right:</span>
                        <input type="text" class="zdfloatingtext" id="zoomd_floatingiconright" name="zoomd_options[floatingiconright]" value="<?php echo $floatingiconright;?>" size="2"/>
                </p>
                <p><input type="checkbox" name="zoomd_options[replacesearchbox]" id="zoomd_replacesearchbox" <?php checked('1', $replacesearchbox); ?>
                    value="1" /><?php _e( 'Replace Wordpress build-in searchbox', 'zoomd-search' );?> <span style=";font-style: italic;color:seagreen">(<?php _e( 'If Disabled the searchbox will use Wordpress search', 'zoomd-search' );?>)</span></p>
                <p><?php _e( 'Search Button Text', 'zoomd-search' );?>: <input type="text" name="zoomd_options[txtcaption]" id="zoomd_txtcaption" value="<?php _e( 'Search', 'zoomd-search' );?>" onkeyup="updatecaption()"  />
                 <a href="javascript:restoredefaultvalues()"><?php _e( 'Restore default', 'zoomd-search' );?></a></p>                
                <div class="zdacewrapper">                    
                    <pre id="editor" class="zdeditor"></pre>
                    <div id="return" class="zdreturn"></div>
                </div>
                <textarea id="zoomd_searchboxhtml" name="zoomd_options[searchboxhtml]" rows="100" cols="100" style="display:none">
                    <?php 
                        if(!empty($searchboxhtml) && !$searchboxhtml==0)
                        {
                            echo $searchboxhtml;
                        }
                        else{
                            echo Settings::searchboxdefaulthtml();
                        }
                    ?>
                </textarea>      
                <span style="padding-left:10px;font-style: italic;color:seagreen">** <?php _e( 'Keep "zoomdsearch" attribute on the elements to trigger the search', 'zoomd-search' );?></span>                        
                <br/>
            </div>
            <!--TS Settings-->
            <h4 class="zdaccordion-toggle"><?php _e( 'Top Searches Settings', 'zoomd-search' );?></h4>
            <div class="zdaccordion-content default">
                <p><input type="checkbox" name="zoomd_options[enabletopsearches]" id="zoomd_enabletopsearches" <?php checked('1', $enabletopsearches); ?>
                    value="1" /><?php _e( 'Add at the bottom of the Post/Page', 'zoomd-search' );?></p>
                <p style="font-style: italic;color:blue"><?php _e( 'Top search bar can be added by adding the', 'zoomd-search' );?> <b>[zoomd_ts/]</b> <?php _e( 'short code at any location inside a post/page', 'zoomd-search' );?></p>
            </div>
        </div> 
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
        </div>
</form>
    <script type="text/javascript">

        
      jQuery(document).ready(function($) {
        //$('#accordion').find('.zdaccordion-toggle').click(function(){

        //Expand or collapse this panel
        //$(this).next().slideToggle('fast');

        //Hide the other panels
        //$(".zdaccordion-content").not($(this).next()).slideUp('fast');

        //});

        //ace init  
        
        var editor = ace.edit("editor");
        editor.setTheme("ace/theme/twilight");
        editor.renderer.setShowGutter(false);
        editor.getSession().setUseWrapMode(true);
        editor.session.setMode("ace/mode/html");
        editor.setValue($("#zoomd_searchboxhtml").val());
        beatify(editor);

        
        editor.on("input", showHTML)

        function showHTML() {
            $('#zoomd_searchboxhtml').text(editor.getValue());
            $('#return').html(editor.getValue());
            $('#zoomd_txtcaption').val($('#zsbtn').val());            
        }
        // or use data: url to handle things like doctype
        function showHTMLInIFrame() {
            $('#return').html("<iframe style='width:400px;height:250px;border:0' src=" +
                "data:text/html," + encodeURIComponent(editor.getValue()) +
                "></iframe>");
            
        }
    });

     function beatify(edtr) {
        var val = edtr.session.getValue();
        val = html_beautify(val, {
                            'indent_inner_html': true,
                            'indent_size': 2,
                            'indent_char': ' ',
                            'wrap_line_length': 78,
                            'brace_style': 'expand',
                            'preserve_newlines': true,
                            'max_preserve_newlines': 5,
                            'indent_handlebars': false,
                            'extra_liners': ['/html']
                        });
        edtr.session.setValue(val);
    }

    function updatecaption() {   
        $("#zsbtn").val($('#zoomd_txtcaption').val());
        var editor = ace.edit("editor");
        var unescapetxt = document.createElement('textarea');
        unescapetxt.innerHTML = $('#return').html();   
        editor.session.setValue(unescapetxt.textContent); 
    }

    function restoredefaultvalues()
    {
        var defhtml = '<?php echo Settings::searchboxdefaulthtml();?>';
        //var defhtml = "dsad";
        var editor = ace.edit("editor");
        editor.session.setValue(defhtml);
        beatify(editor);
        $(zoomd_txtcaption).val("Search");
        // $(zoomd_floatingicontop).val("70");
        // $(zoomd_floatingiconright).val("30");
    }
</script>
<?php   
    $notices= get_option('zoomd_deferred_admin_notices');
    if (!$notices) {       
        echo "<script>
                var $ = jQuery;
                $(document).ready(function() {
                    $('#zoomderrmsg').hide();
                });
            </script>";
        }
    }
}
?>