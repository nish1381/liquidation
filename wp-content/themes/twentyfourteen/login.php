<?php
/*
Template Name: Login
*/

get_header(); ?>


<!-- 
	<h2><?php the_title(); ?></h2>

<form name="loginform" id="loginform" action="<?php echo get_option('home'); ?>/wp-login.php" method="post">
	<p>
		<label>Username<br />
		<input type="text" name="log" id="user_login" class="input" value="" size="20" tabindex="10" /></label>
	</p>
	<p>

		<label>Password<br />
		<input type="password" name="pwd" id="user_pass" class="input" value="" size="20" tabindex="20" /></label>
	</p>
	<p class="forgetmenot"><label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90" /> Remember Me</label></p>
	<p class="submit">
		<input type="submit" name="wp-submit" id="wp-submit" class="button-primary" value="Log In" tabindex="100" />
		<input type="hidden" name="redirect_to" value="<?php echo get_option('home'); ?>/wp-admin/" />

		<input type="hidden" name="testcookie" value="1" />
	</p>
</form>

<p id="nav">
<a href="<?php echo get_option('home'); ?>/wp-login.php?action=lostpassword" title="Password Lost and Found">Lost your password?</a>
</p -->>

<div class="holder-container">
				<div class="box sign">
					<h2>Sign in now,</h2>
					<span class="intro">to continue buying, selling and managing your accounts</span>
					<!-- <form action="#" class="sign-in-form"> -->
					<form name="loginform" class="sign-in-form" id="loginform" action="<?php echo get_option('home'); ?>/wp-login.php" method="post">
						<fieldset>
							<div class="holder">
								<div class="row">
									<label for="text1">Username:</label>
									<div class="align-left">
										<!-- <input type="text" placeholder="" id="text1"> -->
										<input type="text" name="log" id="user_login text1" class="input" value="" size="20" tabindex="10" />
									</div>
								</div>
								<div class="row">
									<label for="text2">Password:</label>
									<div class="align-left">
										<!-- <input type="password" placeholder="" id="text2"> -->
										<input type="password" name="pwd" id="user_pass text2" class="input" value="" size="20" tabindex="20" />
									</div>
								</div>
							</div>
							<div class="holder"><input type="submit" name="wp-submit" id="wp-submit" value="SIGN IN" class="btn green button-primary">
								<!-- <input type="submit" name="wp-submit" id="wp-submit" class="button-primary" value="Log In" tabindex="100" /> -->
							</div>
						</fieldset>
					</form>
					<span class="l-hidden">Forgot <a href="sign-in-alt.html">Password</a> or <a href="sign-in-alt.html">Username?</a></span>
					<div class="has-drops xl-hidden l-visible">Forgot 
						<div class="has-drops-holder"><a href="#">Password</a>
							<div class="drop">
								<section class="prompt-block">
									<div class="holder">
										<h2>Forgot your Password</h2>
										<div class="close"><a href="#">close</a></div>
									</div>
									<span class="intro">Enter your username or E-mail address below and click the “Send” button. In a few minutes, you will receive an E-mail with instructions on how to reset your password.</span>
									<form action="#" class="reminder-form">
										<fieldset>
											<div class="row-out">
												<label for="name">Username:</label>
												<div class="row">
													<input type="text" placeholder="" id="name">
													<input type="submit" value="send" class="btn green">
												</div>
											</div>
											<div class="separator"><span class="or">or</span></div>
											<div class="row-out">
												<label for="email">Email Address:</label>
												<div class="row">
													<input type="text" placeholder="" id="email">
													<input type="submit" value="send" class="btn green">
												</div>
											</div>
										</fieldset>
									</form>
								</section>
							</div>
						</div> or 
						<div class="has-drops-holder alt"><a href="#">Username?</a>
							<div class="drop">
								<section class="prompt-block">
									<div class="holder">
										<h2>Forgot your Username</h2>
										<div class="close"><a href="#">close</a></div>
									</div>
									<span class="intro">Enter your E-mail address and we will send it to you.</span>
									<form action="#" class="reminder-form">
										<fieldset>
											<div class="row-out">
												<label for="address">Your Email Address:</label>
												<div class="row">
													<input type="text" placeholder="" id="address">
													<input type="submit" value="send" class="btn green">
												</div>
											</div>
										</fieldset>
									</form>
								</section>
							</div>
						</div>
					</div>
					<strong class="question">If you have additional questions about your account, please contact us at <a href="#">support@Directliquidation.com</a> or <a href="#">(800)&nbsp;498&nbsp;-&nbsp;1909</a></strong>
				</div>

				<!--box registration starts-->
				<div class="box registration">
					<h2>Not registered yet?</h2>
					<span class="intro">Register now and get started today!</span>
					<ul class="list">
						<li>Buy or sell inventory at a fraction of wholesale cost.</li>
						<li>Over 600 product categories.</li>
						<li>We are publicly traded (NASDAQ, LQDT).</li>
						<li>More that one million buyers use DirectLiquidation for their inventory needs.</li>
					</ul>
					<a href="#" class="btn green">REGISTER NOW</a>
				</div>
				<!--box registration ends-->

		</div>

					<!--social list starts-->
			<ul class="social-list">
				<li class="facebook"><a href="#">facebook</a></li>
				<li class="twitter"><a href="#">twitter</a></li>
				<li class="google"><a href="#">google</a></li>
			</ul>
			<!--social list ends-->



<?php
get_footer();