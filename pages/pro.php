<?

$root_path = dirname(dirname(__FILE__));
include_once $root_path . '/common/header.php';

$action = form_get('action', 'alpha');
$stripe_token = form_post('stripeToken', '');

if ($action == 'subscribe' && $stripe_token && user_exists()) {
    // Subscribe the user to the UpcomingTasks Pro subscription plan

    include_once $root_path . '/classes/Stripe/init.php';

    try {
        \Stripe\Stripe::setApiKey($GLOBALS['stripe_api_secret']);

        $customer = \Stripe\Customer::create(
            array(
                "source"        => $stripe_token,
                "description"   => user_name() . ' (' . user_id() . ' in ' . user_account() . ')',
                "email"         => user_email(),
                "plan"          => $GLOBALS['stripe_plan_name']
            )
        );

        user_stripe_set($customer->id);
        pro_user_set();

        $message = '<p class="notification success">Thanks for your support, your account has been upgraded!</p>';
    } catch (Exception $e) {
        error_handle('subscribe', $e->getMessage(), $_SERVER['SCRIPT_FILENAME'], '8');
    }
} elseif ($action == 'unsubscribe' && user_exists() && user_stripe_get()) {
    // Unsubscribe the current user from the UpcomingTasks Pro subscription plan

    include_once $root_path . '/classes/Stripe/init.php';

    try {
        \Stripe\Stripe::setApiKey($GLOBALS['stripe_api_secret']);

        $customer = \Stripe\Customer::retrieve(user_stripe_get());
        $customer->delete();

        user_stripe_remove();
        pro_user_remove();

        $message = '<p class="notification success">Your account was successfully downgraded to the free account.</p>';
    } catch (Exception $e) {
        error_handle('unsubscribe', $e->getMessage(), $_SERVER['SCRIPT_FILENAME'], '8');
    }
}
?>

<h2><?= $app_name ?> Pro</h2>

<?
if (isset($message)) {
    echo $message;
}
?>

<? if (!pro_user()) { ?>
<p>For a small monthly cost, an <?= $app_name ?> Pro account gives access to additional features, including:</p>
<? } ?>

<ul class="features">
    <li class="released">Editing project details</li>
    <li class="released">Assign tasks to other people</li>
    <li class="released">User profile images in comments</li>
    <li class="released">Time zone localisation support</li>
    <li class="planned">Add attachments to comments</li>
    <li class="planned">Show posted &amp; updated times for comments</li>
    <li class="planned">Push notifications</li>
    <li class="planned">Convert Basecamp links to <?= $app_name ?> links</li>
    <li class="planned">Inline third-party service previews</li>
    <li class="planned">Calendar view</li>
    <li class="planned">Add Markdown formatting support to task comments</li>
    <li class="planned">Order projects by starred</li>
</ul>

<? if (pro_user()) { ?>

<h3>Manage my subscription</h3>
<p>You have an active <?= $app_name ?> Pro subscription, thanks for supporting the service!</p>
<p>If you'd like to cancel your subscription, just hit the button below. There are no cancellation charges.</p>
<p class="buttons">
    <a class="unsubscribe" href="/pages/pro.php?action=unsubscribe">Downgrade to Free account</a>
</p>

<? } else { ?>

<h3>What about the free account?</h3>
<p>All current <?= $app_name ?> features will continue to work as they are for the life of the product. Your data will never be shared with any third-party and there will never be advertisements to get in the way.</p>

<h3>Why should I subscribe?</h3>
<p><?= $app_name ?> was created as a side-project in September 2012 by <a href="http://brendan.murty.id.au/">Brendan Murty</a>. Along with access to additional features, paying for an <?= $app_name ?> Pro account helps support future development and pay for ongoing maintenance costs.</p>

<h3>What if I want to cancel or pause my subscription?</h3>
<p>You're free to downgrade to a free account at any time. There is no cancellation fee. You can re-subscribe again whenever you like.</p>

<h3>How do I subscribe?</h3>

<? if (!user_exists()) { ?>

<p><a class="button login" href="/pages/login.php">Login with your Basecamp account</a> first to upgrade your account!</p>

<? } else { ?>

<p>It's easy, hit the button below to pay via Stripe.</p>

<form action="/pages/pro.php?action=subscribe" method="post" class="subscribe">
  <script
    src="https://checkout.stripe.com/checkout.js" class="stripe-button"
    data-key="<?= $GLOBALS['stripe_api_public'] ?>"
    data-image="https://s3.amazonaws.com/stripe-uploads/acct_157vK6HN1f5gMCcJmerchant-icon-1440476766712-upcomingtasks-336.png"
    data-name="UpcomingTasks Pro"
    data-description="Subscription (AU$6 per month)"
    data-currency="aud"
    data-amount="600"
    data-locale="auto"
    data-email="<?= user_email() ?>"
    data-panel-label="Subscribe"
    data-label="Subscribe to <?= $app_name ?> Pro"
    data-allow-remember-me="false">
  </script>
</form>

<?
}
}

include_once $root_path . '/common/footer.php';
?>
