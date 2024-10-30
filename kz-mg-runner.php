<?php
/*
Plugin Name: Minigame Maker - Runner
Plugin URI: http://kaz-web.me/runner
description: You can create your own 'runner' mini-game for your site.
Version: 1.0.1
Author: Kaz
Author URI: http://kaz-web.me
License: GPL2
Text Domain: kz-mg-runner
Domain Path: /languages
*/

/**
 * @desc Register uninstall hook when the plugin is activated
 */
function kz_mg_runner_activate(){
    register_uninstall_hook( __FILE__, 'kz_mg_runner_uninstall' );
}
register_activation_hook( __FILE__, 'kz_mg_runner_activate' );

/**
 * @desc Delete kz_mg_runner option when the plguin is uninstalled
 */
function kz_mg_runner_uninstall(){
    delete_option('kz_mg_runner');
}

function kz_mg_runner_load_textdomain() {
	load_plugin_textdomain( 'kz_mg_runner', false, dirname( plugin_basename(__FILE__) ) . '/language/' );
}
add_action('plugins_loaded', 'kz_mg_runner_load_textdomain');

/**
* @desc   Return the option for a key
* @param  str $key
* @return str/int - the default option
*/
function kz_mg_runner_get_default_options($key) {
    $options = array(
        'final_level' => 10,
        'thanks' => '',
        'game_page' => '',
        'runner_img_1' => plugins_url( 'src/img/runner_img1.png', __FILE__ ),
        'runner_img_2' => plugins_url( 'src/img/runner_img2.png', __FILE__ ),
        'runner_img_jump' => plugins_url( 'src/img/runner_jump.png', __FILE__ ),
        'runner_img_goal' => plugins_url( 'src/img/runner_goal.png', __FILE__ ),
        'goal_img' => plugins_url( 'src/img/flag.png', __FILE__ ),
        'obs_img_1' => plugins_url( 'src/img/obs1.png', __FILE__ ),
        'obs_img_2' => plugins_url( 'src/img/obs2.png', __FILE__ ),
        'bg_img' => plugins_url( 'src/img/bg.png', __FILE__ ),
        'text_color' => '#2c2c2c',
        'ground_color' => '#8b4513',
        'twitter' => ''
    );
    if ($key != 'all') {
        return $options[$key];
    } else {
        return $options;
    }
}

/**
* @desc Add 'Mini Games' menu to the admin menu if it is not already done by the other plugins
*/


if ( empty ( $GLOBALS['admin_page_hooks']['kg-mini-game-menu'] ) ) {
    function mini_game_main_menu() {
        add_menu_page( esc_html__('Mini Games', 'kz_mg_runner'), esc_html__('Mini Games', 'kg-mg-runner'), 'manage_options', 'kg-mini-game-menu', '', 'dashicons-heart', 58);
        add_submenu_page( 'kg-mini-game-menu', esc_html__('Mini Games', 'kz_mg_runner'), esc_html__('Runner', 'kz_mg_runner'), 'manage_options', 'kg-mini-game-menu', '' );
        remove_submenu_page('kg-mini-game-menu','kg-mini-game-menu');
    }
    add_action( 'admin_menu', 'mini_game_main_menu', 10 );
}

/**
* @desc Add 'Runner' submenu to the top level menu item
*/
function kg_mg_runner_menu(){
    $page_hook_suffix = add_submenu_page( 'kg-mini-game-menu', esc_html__('Runner', 'kz_mg_runner'), esc_html__('Runner', 'kz_mg_runner'), 'manage_options', 'kg-mg-runner-menu','kz_mg_runner_settings');
    add_action( 'admin_print_scripts-'.$page_hook_suffix, 'kz_mg_runner_load_scripts_admin' );
}

/**
* @desc Load scripts and style sheets
*/
function kz_mg_runner_load_scripts_admin() {
    wp_enqueue_script( 'kz-mg-runner-admin-css', plugins_url( 'src/js/admin.js', __FILE__ ), array('jquery'), '0.1', true );
    wp_enqueue_media();
    wp_enqueue_style( 'kz-mg-runner-admin-css', plugins_url( 'src/css/admin.css', __FILE__ ), array(), '0.1', '' );
}
add_action( 'admin_menu', 'kg_mg_runner_menu' );

/**
* @desc Set up options if it does not exist
*/
function kz_mg_runner_options_init() {
    $kz_mg_runner_options = get_option( 'kz_mg_runner' );
    if ( $kz_mg_runner_options === false ) {
        $kz_mg_runner_options = kz_mg_runner_get_default_options('all');
        add_option( 'kz_mg_runner', $kz_mg_runner_options, '', 'no' );
    }
}
add_action( 'after_setup_theme', 'kz_mg_runner_options_init' );

/**
* @desc   Return the value if it exists
* @param  str $key - The key to get options
* @return str|int The value, false if no value
*/
function kz_mg_runner_options($key) {
    $kz_mg_runner_options = get_option( 'kz_mg_runner' );
    if (isset($kz_mg_runner_options[$key])) {
        return $kz_mg_runner_options[$key];
    } else {
        return false;
    }
}

/**
* @desc   Return true or false if the key is set to the option
* @param  str $key - The key to options to be checked
* @return bool - true or false
*/
function isset_kz_mg_runner_options($key) {
    $kz_mg_runner_options = get_option( 'kz_mg_runner' );
    return isset($kz_mg_runner_options[$key]);
}

/**
* @desc Register settings
*/
function kz_mg_runner_settings_init() {
    // Basic Settings
    register_setting( 'kz_mg_runner', 'kz_mg_runner', 'kz_mg_runner_validate' );
    add_settings_section('kz_mg_runner_basic_section', esc_html__('Game Settings', 'kz_mg_runner'), 'kz_mg_runner_basic_header_text', 'kz_mg_runner');
    add_settings_field('kz_mg_runner_basic_levels', esc_html__('Number of Stages', 'kz_mg_runner'), 'kz_mg_runner_basic_levels', 'kz_mg_runner', 'kz_mg_runner_basic_section');
    add_settings_field('kz_mg_runner_basic_game_page', esc_html__('Game Page', 'kz_mg_runner'), 'kz_mg_runner_basic_game_page', 'kz_mg_runner', 'kz_mg_runner_basic_section');
    add_settings_field('kz_mg_runner_basic_thanks_page', esc_html__('Page after Goal', 'kz_mg_runner'), 'kz_mg_runner_basic_thanks_page', 'kz_mg_runner', 'kz_mg_runner_basic_section');
    add_settings_field('kz_mg_runner_basic_twitter', esc_html__('Twitter Account', 'kz_mg_runner'), 'kz_mg_runner_basic_twitter', 'kz_mg_runner', 'kz_mg_runner_basic_section');
    // Image Settings
    add_settings_section('kz_mg_runner_image_section', esc_html__('Image Settings', 'kz_mg_runner'), 'kz_mg_runner_image_header_text', 'kz_mg_runner');
    add_settings_field('kz_mg_runner_image_runner_img_1', esc_html__('Runner Image 1', 'kz_mg_runner'), 'kz_mg_runner_image_runner_img_1', 'kz_mg_runner', 'kz_mg_runner_image_section');
    add_settings_field('kz_mg_runner_image_runner_img_2', esc_html__('Runner Image 2', 'kz_mg_runner'), 'kz_mg_runner_image_runner_img_2', 'kz_mg_runner', 'kz_mg_runner_image_section');
    add_settings_field('kz_mg_runner_image_runner_jump', esc_html__('Runner Image - Jumping', 'kz_mg_runner'), 'kz_mg_runner_image_runner_jump', 'kz_mg_runner', 'kz_mg_runner_image_section');
    add_settings_field('kz_mg_runner_image_runner_goal', esc_html__('Runner Image - Goal', 'kz_mg_runner'), 'kz_mg_runner_image_runner_goal', 'kz_mg_runner', 'kz_mg_runner_image_section');
    add_settings_field('kz_mg_runner_image_goal', esc_html__('Goal Image', 'kz_mg_runner'), 'kz_mg_runner_image_goal', 'kz_mg_runner', 'kz_mg_runner_image_section');
    add_settings_field('kz_mg_runner_image_obs_1', esc_html__('Obstacle Image 1', 'kz_mg_runner'), 'kz_mg_runner_image_obs_1', 'kz_mg_runner', 'kz_mg_runner_image_section');
    add_settings_field('kz_mg_runner_image_obs_2', esc_html__('Obstacle Image 2', 'kz_mg_runner'), 'kz_mg_runner_image_obs_2', 'kz_mg_runner', 'kz_mg_runner_image_section');
    add_settings_field('kz_mg_runner_image_bg', esc_html__('Background Image', 'kz_mg_runner'), 'kz_mg_runner_image_bg', 'kz_mg_runner', 'kz_mg_runner_image_section');
    add_settings_field('kz_mg_runner_image_ground', esc_html__('Ground Color', 'kz_mg_runner'), 'kz_mg_runner_image_ground', 'kz_mg_runner', 'kz_mg_runner_image_section');
    add_settings_field('kz_mg_runner_image_text', esc_html__('Text Color', 'kz_mg_runner'), 'kz_mg_runner_image_text', 'kz_mg_runner', 'kz_mg_runner_image_section');
}
add_action( 'admin_init', 'kz_mg_runner_settings_init' );


/**
 * @desc Fuctions to add settings to the admin page
 */
function kz_mg_runner_basic_header_text() {
esc_html_e('Basic settings of the runner game', 'kz_mg_runner');
}
function kz_mg_runner_basic_levels() {
    $final_level = (isset_kz_mg_runner_options('final_level')) ? kz_mg_runner_options('final_level') : kz_mg_runner_get_default_options('final_level');
?>
    <div class="kz-mg-section-input-field">
        <input id="kz-mg-runner-final-level" name="kz_mg_runner[final_level]" value="<?php echo $final_level; ?>" min="2" max="11" type="number">
        <div class="kz-mg-section-input-tips">
            <p>
                <?php esc_html_e('Please choose a number between 2 and 11.', 'kz_mg_runner'); ?>
                <br>
                <?php esc_html_e('10 is recommended since 11 is almost impossible to complete.', 'kz_mg_runner'); ?>
            </p>
        </div>
    </div>
<?php
}
function kz_mg_runner_basic_game_page() {
    $game_page = (isset_kz_mg_runner_options('game_page')) ? kz_mg_runner_options('game_page') : kz_mg_runner_get_default_options('game_page');
?>
    <div class="kz-mg-section-input-field">
        <span><?php echo home_url('/'); ?></span>
        <input id="kz-mg-runner-game-page" name="kz_mg_runner[game_page]" value="<?php echo $game_page; ?>">
        <div class="kz-mg-section-input-tips">
            <p>
                <?php esc_html_e('Please input the url of a page where the game will be.', 'kz_mg_runner'); ?>
                <br>
                <?php esc_html_e('Include the shortcode in the page.', 'kz_mg_runner'); ?>
            </p>
        </div>
    </div>
<?php
}
function kz_mg_runner_basic_thanks_page() {
    $thanks = (isset_kz_mg_runner_options('thanks')) ? kz_mg_runner_options('thanks') : kz_mg_runner_get_default_options('thanks');
?>
    <div class="kz-mg-section-input-field">
        <span><?php echo home_url('/'); ?></span>
        <input id="kz-mg-runner-thanks" name="kz_mg_runner[thanks]" value="<?php echo $thanks; ?>">
        <div class="kz-mg-section-input-tips">
            <p>
                <?php esc_html_e('Please input the url of a page to be transitioned after the goal.', 'kz_mg_runner'); ?>
                <br>
                <?php esc_html_e('Also, don\'t forget to include the shortcode in the page.', 'kz_mg_runner'); ?>
            </p>
        </div>
    </div>
<?php
}
function kz_mg_runner_basic_twitter() {
    $twitter = (isset_kz_mg_runner_options('twitter')) ? kz_mg_runner_options('twitter') : kz_mg_runner_get_default_options('twitter');
?>
    <div class="kz-mg-section-input-field">
        <input id="kz-mg-runner-twitter" name="kz_mg_runner[twitter]" value="<?php echo $twitter; ?>" type="text">
        <div class="kz-mg-section-input-tips">
            <p>
                <?php esc_html_e('Your twitter account including @', 'kz_mg_runner'); ?>
            </p>
        </div>
    </div>
<?php
}
function kz_mg_runner_image_header_text() {
    esc_html_e('Image settings of the runner game', 'kz_mg_runner');
}
function kz_mg_runner_image_runner_img_1(){
    $runner_img_1 = (isset_kz_mg_runner_options('runner_img_1')) ? kz_mg_runner_options('runner_img_1') : kz_mg_runner_get_default_options('runner_img_1');
?>
    <div class="kz-mg-section-input-field">
        <img id="kz-mg-runner-runner-img-1-preview" src="<?php echo $runner_img_1; ?>">
        <input id="kz-mg-runner-runner-img-1-input" name="kz_mg_runner[runner_img_1]" value="<?php echo $runner_img_1; ?>" type="hidden">
        <div class="kz-mg-section-input-img-button">
            <button type="submit" class="kz-mg-runner-upload button" data-kz-mg-field="kz-mg-runner-runner-img-1"><?php esc_html_e('Upload', 'kz_mg_runner'); ?></button>
        </div>
        <div class="kz-mg-section-input-tips">
            <p>
                <?php esc_html_e('The propotion must be 4:5.', 'kz_mg_runner'); ?>
                <br>
                <?php esc_html_e('It is recommended to use a transparent png/gif. However, the crash detection area is always rectangle', 'kz_mg_runner'); ?>
            </p>
        </div>
    </div>
<?php
}
function kz_mg_runner_image_runner_img_2(){
    $runner_img_2 = (isset_kz_mg_runner_options('runner_img_2')) ? kz_mg_runner_options('runner_img_2') : kz_mg_runner_get_default_options('runner_img_2');
?>
    <div class="kz-mg-section-input-field">
        <img id="kz-mg-runner-runner-img-2-preview" src="<?php echo $runner_img_2; ?>">
        <input id="kz-mg-runner-runner-img-2-input" name="kz_mg_runner[runner_img_2]" value="<?php echo $runner_img_2; ?>" type="hidden">
        <div class="kz-mg-section-input-img-button">
            <button type="submit" class="kz-mg-runner-upload button" data-kz-mg-field="kz-mg-runner-runner-img-2"><?php esc_html_e('Upload', 'kz_mg_runner'); ?></button>
        </div>
        <div class="kz-mg-section-input-tips">
            <p>
                <?php esc_html_e('An image to make the runner looks like running.', 'kz_mg_runner'); ?>
                <?php esc_html_e('The propotion must be 4:5.', 'kz_mg_runner'); ?>
            </p>
        </div>
    </div>
<?php
}
function kz_mg_runner_image_runner_jump(){
    $runner_img_jump = (isset_kz_mg_runner_options('runner_img_jump')) ? kz_mg_runner_options('runner_img_jump') : kz_mg_runner_get_default_options('runner_img_jump');
?>
    <div class="kz-mg-section-input-field">
        <img id="kz-mg-runner-runner-img-jump-preview" src="<?php echo $runner_img_jump; ?>">
        <input id="kz-mg-runner-runner-img-jump-input" name="kz_mg_runner[runner_img_jump]" value="<?php echo $runner_img_jump; ?>" type="hidden">
        <div class="kz-mg-section-input-img-button">
            <button type="submit" class="kz-mg-runner-upload button" data-kz-mg-field="kz-mg-runner-runner-img-jump"><?php esc_html_e('Upload', 'kz_mg_runner'); ?></button>
        </div>
        <div class="kz-mg-section-input-tips">
            <p>
                <?php esc_html_e('An image of the jumping runner.', 'kz_mg_runner'); ?>
                <?php esc_html_e('The propotion must be 4:5.', 'kz_mg_runner'); ?>
            </p>
        </div>
    </div>
<?php
}
function kz_mg_runner_image_runner_goal(){
    $runner_img_goal = (isset_kz_mg_runner_options('runner_img_goal')) ? kz_mg_runner_options('runner_img_goal') : kz_mg_runner_get_default_options('runner_img_goal');
?>
    <div class="kz-mg-section-input-field">
        <img id="kz-mg-runner-runner-img-goal-preview" src="<?php echo $runner_img_goal; ?>">
        <input id="kz-mg-runner-runner-img-goal-input" name="kz_mg_runner[runner_img_goal]" value="<?php echo $runner_img_goal; ?>" type="hidden">
        <div class="kz-mg-section-input-img-button">
            <button type="submit" class="kz-mg-runner-upload button" data-kz-mg-field="kz-mg-runner-runner-img-goal"><?php esc_html_e('Upload', 'kz_mg_runner'); ?></button>
        </div>
        <div class="kz-mg-section-input-tips">
            <p>
                <?php esc_html_e('An image of the runner after the goal.', 'kz_mg_runner'); ?>
                <?php esc_html_e('The propotion must be 4:5.', 'kz_mg_runner'); ?>
            </p>
        </div>
    </div>
<?php
}
function kz_mg_runner_image_goal(){
    $goal_img = (isset_kz_mg_runner_options('goal_img')) ? kz_mg_runner_options('goal_img') : kz_mg_runner_get_default_options('goal_img');
?>
    <div class="kz-mg-section-input-field">
        <img id="kz-mg-runner-goal-img-preview" src="<?php echo $goal_img; ?>">
        <input id="kz-mg-runner-goal-img-input" name="kz_mg_runner[goal_img]" value="<?php echo $goal_img; ?>" type="hidden">
        <div class="kz-mg-section-input-img-button">
            <button type="submit" class="kz-mg-runner-upload button" data-kz-mg-field="kz-mg-runner-goal-img"><?php esc_html_e('Upload', 'kz_mg_runner'); ?></button>
        </div>
        <div class="kz-mg-section-input-tips">
            <p>
                <?php esc_html_e('An image of the goal.', 'kz_mg_runner'); ?>
                <?php esc_html_e('The propotion must be 7:30.', 'kz_mg_runner'); ?>
            </p>
        </div>
    </div>
<?php
}
function kz_mg_runner_image_obs_1(){
    $obs_img_1 = (isset_kz_mg_runner_options('obs_img_1')) ? kz_mg_runner_options('obs_img_1') : kz_mg_runner_get_default_options('obs_img_1');
?>
    <div class="kz-mg-section-input-field">
        <img id="kz-mg-runner-obs_img_1-preview" src="<?php echo $obs_img_1; ?>">
        <input id="kz-mg-runner-obs_img_1-input" name="kz_mg_runner[obs_img_1]" value="<?php echo $obs_img_1; ?>" type="hidden">
        <div class="kz-mg-section-input-img-button">
            <button type="submit" class="kz-mg-runner-upload button" data-kz-mg-field="kz-mg-runner-obs_img_1"><?php esc_html_e('Upload', 'kz_mg_runner'); ?></button>
        </div>
        <div class="kz-mg-section-input-tips">
            <p>
                <?php esc_html_e('An image of the obstacle.', 'kz_mg_runner'); ?>
                <?php esc_html_e('The propotion must be 2:4.', 'kz_mg_runner'); ?>
                <?php esc_html_e('TIP: To make it look like floating, give some space at the bottom.', 'kz_mg_runner'); ?>
            </p>
        </div>
    </div>
<?php
}
function kz_mg_runner_image_obs_2(){
    $obs_img_2 = (isset_kz_mg_runner_options('obs_img_2')) ? kz_mg_runner_options('obs_img_2') : kz_mg_runner_get_default_options('obs_img_2');
?>
    <div class="kz-mg-section-input-field">
        <img id="kz-mg-runner-obs_img_2-preview" src="<?php echo $obs_img_2; ?>">
        <input id="kz-mg-runner-obs_img_2-input" name="kz_mg_runner[obs_img_2]" value="<?php echo $obs_img_2; ?>" type="hidden">
        <div class="kz-mg-section-input-img-button">
            <button type="submit" class="kz-mg-runner-upload button" data-kz-mg-field="kz-mg-runner-obs_img_2"><?php esc_html_e('Upload', 'kz_mg_runner'); ?></button>
        </div>
        <div class="kz-mg-section-input-tips">
            <p>
                <?php esc_html_e('An image of the obstacle.', 'kz_mg_runner'); ?>
                <?php esc_html_e('The propotion must be 2:4.', 'kz_mg_runner'); ?>
            </p>
        </div>
    </div>
<?php
}
function kz_mg_runner_image_bg(){
    $bg_img = (isset_kz_mg_runner_options('bg_img')) ? kz_mg_runner_options('bg_img') : kz_mg_runner_get_default_options('bg_img');
?>
    <div class="kz-mg-section-input-field">
        <img id="kz-mg-runner-bg_img-preview" src="<?php echo $bg_img; ?>">
        <input id="kz-mg-runner-bg_img-input" name="kz_mg_runner[bg_img]" value="<?php echo $bg_img; ?>" type="hidden">
        <div class="kz-mg-section-input-img-button">
            <button type="submit" class="kz-mg-runner-upload button" data-kz-mg-field="kz-mg-runner-bg_img"><?php esc_html_e('Upload', 'kz_mg_runner'); ?></button>
        </div>
        <div class="kz-mg-section-input-tips">
            <p>
                <?php esc_html_e('An image of the background.', 'kz_mg_runner'); ?>
                <?php esc_html_e('The propotion must be 2:1.', 'kz_mg_runner'); ?>
            </p>
        </div>
    </div>
<?php
}
function kz_mg_runner_image_ground(){
    $ground_color = (isset_kz_mg_runner_options('ground_color')) ? kz_mg_runner_options('ground_color') : kz_mg_runner_get_default_options('ground_color');
?>
    <div class="kz-mg-section-input-field">
        <input id="kz-mg-runner-ground_color-input" name="kz_mg_runner[ground_color]" value="<?php echo $ground_color; ?>" type="color">
        <div class="kz-mg-section-input-tips">
            <p>
                <?php esc_html_e('The color of the ground.', 'kz_mg_runner'); ?>
                <?php esc_html_e('Currently, images are not supported.', 'kz_mg_runner'); ?>
            </p>
        </div>
    </div>
<?php
}
function kz_mg_runner_image_text(){
    $text_color = (isset_kz_mg_runner_options('text_color')) ? kz_mg_runner_options('text_color') : kz_mg_runner_get_default_options('text_color');
?>
    <div class="kz-mg-section-input-field">
        <input id="kz-mg-runner-text_color-input" name="kz_mg_runner[text_color]" value="<?php echo $text_color; ?>" type="color">
        <div class="kz-mg-section-input-tips">
            <p>
                <?php esc_html_e('The color the texts.', 'kz_mg_runner'); ?>
                <?php esc_html_e('Currently, images are not supported.', 'kz_mg_runner'); ?>
            </p>
        </div>
    </div>
<?php
}
/**
* @desc Display setting page
*/
function kz_mg_runner_settings(){
?>
    <div class="wrap">
        <h1>
            <?php esc_html_e('Runner Game Settings', 'kz_mg_runner'); ?>
        </h1>
        <div class="kz-mg-section">
            <label><?php esc_html_e('Short Code', 'kz_mg_runner'); ?></label>
            <input type="text" value="[kz_mg_runner]">
        </div>
        <form id="kz-mg-runner-form" action="options.php" method="post" enctype="multipart/form-data">
            <?php
                 settings_fields( 'kz_mg_runner' );
                 do_settings_sections('kz_mg_runner');
            ?>
            <p class="submit">
                <input name="kz_mg_runner[submit]" id="submit_options_form" type="submit" class="button-primary" value="<?php esc_html_e('Save', 'kz_mg_runner') ?>" />
            </p>
        </form>
    </div>
<?php
}

/**
 * @desc   Validation and sanitisation of input data before saving
 * @param  arr $input - input data
 * @return arr $valid_input - validated and sanitised data
 */
function kz_mg_runner_validate( $input ) {
    $default_options = kz_mg_runner_get_default_options('all');
    $valid_input = $default_options;
    
    $submit = ! empty($input['submit']) ? true : false;
 
    if ( $submit ) {
        if ($input['final_level'] < 2) {
            $valid_input['final_level'] = 2;
        } else if ($input['final_level'] > 12) {
            $valid_input['final_level'] = 12;
        } else {
            $valid_input['final_level'] = esc_html(floor($input['final_level']));
        }
        $valid_input['thanks'] = esc_html($input['thanks']);
        $valid_input['game_page'] = esc_html($input['game_page']);
        $valid_input['runner_img_1'] = esc_html($input['runner_img_1']);
        $valid_input['runner_img_2'] = esc_html($input['runner_img_2']);
        $valid_input['runner_img_jump'] = esc_html($input['runner_img_jump']);
        $valid_input['runner_img_goal'] = esc_html($input['runner_img_goal']);
        $valid_input['goal_img'] = esc_html($input['goal_img']);
        $valid_input['obs_img_1'] = esc_html($input['obs_img_1']);
        $valid_input['obs_img_2'] = esc_html($input['obs_img_2']);
        $valid_input['bg_img'] = esc_html($input['bg_img']);
        $valid_input['ground_color'] = esc_html($input['ground_color']);
        $valid_input['text_color'] = esc_html($input['text_color']);
        $valid_input['twitter'] = esc_html($input['twitter']);
    }
 
    return $valid_input;
}

/**
 * @desc Enqueue scripts and styles for shortcode use
 */
function kz_mg_runner_shortcode_wp_enqueue_scripts() {
    wp_register_style( 'kz-mg-runner-front-css', plugins_url('src/css/kz-mg-runner-front.css', __FILE__), array(), '1.0.3', 'all' );
    wp_register_script( 'kz-mg-runner-front-js', plugins_url('src/js/kz-mg-runner-front.js', __FILE__), array(), '1.0.0', true );
    wp_register_script( 'kz-mg-runner-game', plugins_url('src/js/kz-mg-runner.js', __FILE__), array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'kz_mg_runner_shortcode_wp_enqueue_scripts' );

/**
 * @desc Shortcode to display game/thanks page
 */
function kz_mg_runner_shortcode() {
    wp_enqueue_style( 'kz-mg-runner-front-css' );
    wp_enqueue_script('kz-mg-runner-front-js');
    $kz_mg_runner_options = get_option( 'kz_mg_runner' );
    
    $pid = url_to_postid( home_url('/').$kz_mg_runner_options['thanks'] );
    
    if (get_the_ID() == $pid) {
        $goalImage = sanitize_text_field($_POST['image']);
        $content = ' <div id="kz-mg-runner-goal-img"><img src="'.$goalImage.'"></div>';
        $content .= '
            <div id="kz-mg-runner-sns">
                <p>Share Your Score</p>
                <div id="kz-mg-runner-sns-links">
                    <a href="'.$goalImage.'" id="kz-mg-runner-sns-save" download="final-score.png">'.esc_html__('Save as Image', 'kz_mg_runner').'</a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u='.urlencode(home_url('/').$kz_mg_runner_options['game_page']).'" id="kz-mg-runner-sns-fb" target="_blank"><img src="'.plugins_url('src/img/sns-fb.png', __FILE__).'" alt="facebook"></a>
                    <a href="https://twitter.com/intent/tweet?via='.substr($kz_mg_runner_options['twitter'],1).'&text='.urlencode(home_url('/').$kz_mg_runner_options['game_page']).'" id="kz-mg-runner-sns-tw" target="_blank"><img src="'.plugins_url('src/img/sns-tw.png', __FILE__).'" alt="twitter"></a>
                </div>
            </div>';
    } else {
        wp_enqueue_script('kz-mg-runner-game');
        $content = '<div id="kz-mg-runner-wrap" data-final-level="'.$kz_mg_runner_options['final_level'].'" data-thanks="'.home_url('/').$kz_mg_runner_options['thanks'].'" data-img1="'.$kz_mg_runner_options['runner_img_1'].'" data-img2="'.$kz_mg_runner_options['runner_img_2'].'" data-runner-jump="'.$kz_mg_runner_options['runner_img_jump'].'" data-runner-goal="'.$kz_mg_runner_options['runner_img_goal'].'" data-goal="'.$kz_mg_runner_options['goal_img'].'" data-obs1="'.$kz_mg_runner_options['obs_img_1'].'" data-obs2="'.$kz_mg_runner_options['obs_img_2'].'" data-bg="'.$kz_mg_runner_options['bg_img'].'" data-text="'.$kz_mg_runner_options['text_color'].'" data-ground="'.$kz_mg_runner_options['ground_color'].'" data-twitter="'.$kz_mg_runner_options['twitter'].'">
                        <canvas id="kz-mg-runner"></canvas>
                        <div id="kz-mg-runner-goal-text"></div>
                        <div id="kz-mg-runner-loader" style="display: none;"></div>
                        <div id="kz-mg-runner-sns" class="off">
                            <p>Share Your Score</p>
                            <div id="kz-mg-runner-sns-links">
                                <a href="#" id="kz-mg-runner-sns-save" download="myscore.png">'.esc_html__('Save as Image', 'kz_mg_runner').'</a>
                                <a href="https://www.facebook.com/sharer/sharer.php?u='.urlencode(get_permalink()).'" id="kz-mg-runner-sns-fb" target="_blank"><img src="'.plugins_url('src/img/sns-fb.png', __FILE__).'" alt="facebook"></a>
                                <a href="https://twitter.com/intent/tweet?via='.substr($kz_mg_runner_options['twitter'],1).'&text='.urlencode(get_permalink()).'" id="kz-mg-runner-sns-tw" target="_blank"><img src="'.plugins_url('src/img/sns-tw.png', __FILE__).'" alt="twitter"></a>
                            </div>
                        </div>
                        <div id="kz-mg-runner-nonce">'.wp_nonce_field( 'kz_mg_runner_nonce_action', 'kz_mg_runner_nonce' ).'</div>
                    </div>';
    }
	return $content;
}
add_shortcode( 'kz_mg_runner', 'kz_mg_runner_shortcode' );


/**
 * @desc Filter the content if a user is accessing the thanks page directly
 * @param $content - content of the page
 * @return $custom_content - if a user is accessing the page directly, 
 *                           display a custom message. If not, display the content.
 */
function kz_mg_runner_filter_the_content( $content ) {
    $custom_content = $content;
    $kz_mg_runner_options = get_option( 'kz_mg_runner' );
    $pid = url_to_postid( home_url('/').$kz_mg_runner_options['thanks'] );
    
    if (get_the_ID() == $pid) {
        if ( 
            ! isset( $_POST['kz_mg_runner_nonce'] ) 
            || ! wp_verify_nonce( $_POST['kz_mg_runner_nonce'], 'kz_mg_runner_nonce_action' ) 
        ) {
            $custom_content = esc_html__('Sorry! You need to complete the game to see this page!', 'kz_mg_runner');
            $custom_content .= '<br><a href="'.$kz_mg_runner_options['game_page'].'">'.esc_html__('Let\'s play the game!', 'kz_mg_runner').'</a>';
        }
    }
    return $custom_content;
}
add_filter( 'the_content', 'kz_mg_runner_filter_the_content' );
