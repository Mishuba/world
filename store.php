<?php
session_start();
require 'config.php';
use Stripe\Stripe;

Stripe::setApiKey(STRIPE_SECRET_KEY);

>