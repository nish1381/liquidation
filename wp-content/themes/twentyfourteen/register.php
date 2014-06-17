<?php
/*
Template Name: register
*/

get_header(); ?>


			<!--box register starts-->
			<div class="box register">
				<h2>Register</h2>
				<form action="#" class="registration-form">
					<fieldset>
						<div class="row">
							<div class="col">
								<label for="user">Username:</label>
								<input type="text" id="user" placeholder="">
							</div>
							<div class="col">
								<label for="password">Password:</label>
								<input type="password" placeholder="" id="password">
							</div>
						</div>
						<div class="row">
							<div class="col">
								<label for="mail">Email:</label>
								<input type="email" id="mail" placeholder="">
							</div>
							<div class="col">
								<label for="phone">Phone:</label>
								<input type="text" placeholder="" id="phone">
							</div>
						</div>
						<div class="row">
							<div class="col">
								<label for="name1">Contact Name:</label>
								<input type="text" id="name1" placeholder="">
							</div>
							<div class="col">
								<label for="name2">Business Name:</label>
								<input type="text" placeholder="" id="name2">
							</div>
						</div>
						<div class="row">
							<div class="col">
								<label for="country">Country:</label>
								<select data-pair="#state" id="country">
									<option selected>USA</option>
									<option data-choice="null">Germany</option>
									<option data-choice="null">Spain</option>
								</select>
							</div>
							<div class="col">
								<label for="state">State:</label>
								<select id="state">
									<option selected>New York</option>
								</select>
							</div>
						</div>
						<div class="row-2">
							<input type="submit" value="CREATE ACCOUNT" class="btn green">
						</div>
					</fieldset>
				</form>
				<div class="holder">
					<p>By clicking on the “Create Account” button you are bound to the <a href="#">Terms of Service</a> and <a href="#">User Agreement</a> and <a href="#">Privacy Policy</a>.</p>
					<p>I may receive communications from DirectLiquidation.com and/or The Recon Group Inc., and can change my notification preferences in My Account.</p>
				</div>
			</div>
			<!--box register ends-->
			<!--social list starts-->
			<ul class="social-list alt">
				<li class="facebook"><a href="#">facebook</a></li>
				<li class="twitter"><a href="#">twitter</a></li>
				<li class="google"><a href="#">google</a></li>
			</ul>



<?php
get_footer();