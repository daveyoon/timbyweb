<?php
/* 
 * Login Page 
 * 
 * 
 * @package wordpress 
 * @subpackage Blackbook 
 * @version 0.0.1 
 */

if( !empty($_POST) && wp_verify_nonce( $_POST['_nonce'], 'timbyfrontlogin') == true ){ 
  if( isset($_POST['log']) && isset($_POST['pwd']) ) {

    // remember this user?
    if(isset($_POST['remember_me'])){
      $rememberme = ( sanitize_text_field($_POST['log']) == 'forever');
    }

    $user = wp_signon( 
      array( 
      'user_login' => sanitize_text_field($_POST['log']), 
      'user_password' => sanitize_text_field($_POST['pwd'])
      ), 
      $rememberme
    ); 

    if( !is_wp_error($user) ) {
      wp_safe_redirect( site_url('/') );
    } 
  } 
}

get_header();
?>

<?php get_header(); ?>
<nav class="navbar" role="navigation">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="#">Timby</a>
    </div>
  </div>
</nav>
<div class="col-md-5">
<form class="form-horizontal" role="form" action="<?php echo esc_url( home_url('/sign-in') ) ?>" method="post">
  <?php if( isset($user) && $user->get_error_message() ) { ?> 
    <div class="alert alert-warning">Invalid login</div> 
  <?php } ?>
  <div class="form-group">
    <label for="inputEmail3" class="col-sm-2 control-label">Login</label>
    <div class="col-sm-10">
      <input type="text" name="log" class="form-control" placeholder="Username/Email" />
    </div>
  </div>
  <div class="form-group">
    <label for="inputPassword3" class="col-sm-2 control-label">Password</label>
    <div class="col-sm-10">
      <input type="password" name="pwd" class="form-control" id="inputPassword3" placeholder="Password">
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <div class="checkbox">
        <label>
          <input name="rememberme" type="checkbox" value="forever"> Remember me?
        </label>
      </div>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <button type="submit" class="btn btn-default">Sign in</button>
    </div>
    <p>
      <a href="<?php echo esc_url(home_url( '/wp-login.php?action=lostpassword' ) ); ?>">Forgot your password?</a>
    </p> 
  </div>

  <input type="hidden" name="redirect_to" value="<?php echo esc_url( home_url('/') ) ?>" />
  <?php wp_nonce_field( 'timbyfrontlogin', '_nonce' ); ?> 
</form>
</div>