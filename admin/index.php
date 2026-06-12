<?php
/*
 |  Snicker Plus – A FlatFile Comment Plugin for Bludit
 |  @file       ./admin/index.php
 |  @author     Steve Harris (Harris Lineage)
 |  @version    1.0.0
 |  @website    https://github.com/harrislineage/snicker-plus
 |  @license    MIT
 |  @copyright  Copyright © 2025 Steve Harris (Harris Lineage)
 */

if (!defined('BLUDIT')) {
    exit('No direct access');
}
global $L, $Snicker;

// Pending Counter
$count = count($Snicker->getIndex("pending"));
$count = ($count > 99) ? "99+" : $count;
$spam = count($Snicker->getIndex("spam"));

// Tab Strings
$strings = array(
    "pending" => sn__("Pending"),
    "approved" => sn__("Approved"),
    "rejected" => sn__("Rejected"),
    "spam" => sn__("Spam"),
    "single" => sn__("Single Comment"),
    "uuid" => sn__("Page Comments"),
    "user" => sn__("User Comments"),
    "users" => sn__("Users"),
    "configure" => sn__("Configuration")
);

// Current Tab
$view = "index";
$commentTabs = array("pending", "approved", "rejected", "spam");
$adminTabs = array("users", "configure");
$tabs = $commentTabs;
$current = isset($_GET["tab"]) ? $_GET["tab"] : "pending";
if (!in_array($current, array_merge($commentTabs, $adminTabs))) {
    $current = "pending";
}
if (isset($_GET["view"]) && in_array($_GET["view"], array("single", "uuid", "user"))) {
    $view = $current = $_GET["view"];
}
?>
<h2 class="mt-0 mb-3">
    <span class="fa fa-comments-o" style="font-size: 0.7em;"></span> Snicker <?php sn_e("Comments"); ?>
</h2>

<ul class="nav nav-tabs" role="tablist">
    <?php foreach ($tabs as $tab) { ?>
        <?php $class = "nav-link nav-{$tab}" . ($current === $tab ? " active" : ""); ?>
        <li class="nav-item">
            <a id="<?php echo $tab; ?>-tab" href="<?php echo DOMAIN_ADMIN; ?>snicker?tab=<?php echo $tab; ?>" class="<?php echo $class; ?>" role="tab">
                <?php
                echo $strings[$tab];
                if ($tab === "pending" && !empty($count)) {
                    ?> <span class="badge badge-primary"><?php echo $count; ?></span><?php
                }
                if ($tab === "spam" && !empty($spam)) {
                    ?> <span class="badge badge-danger"><?php echo $spam; ?></span><?php
                }
                ?>
            </a>
        </li>
    <?php } ?>

    <li class="nav-item">
        <a id="users-tab" href="<?php echo DOMAIN_ADMIN; ?>snicker?tab=users" class="nav-link nav-users<?php echo ($current === "users") ? " active" : ""; ?>" role="tab">
            <span class="fa fa-users"></span><?php sn_e("Users"); ?>
        </a>
    </li>
    <li class="nav-item">
        <a id="configure-tab" href="<?php echo DOMAIN_ADMIN; ?>snicker?tab=configure" class="nav-link nav-config<?php echo ($current === "configure") ? " active" : ""; ?>" role="tab">
            <span class="fa fa-gear"></span><?php sn_e("Configuration"); ?>
        </a>
    </li>
</ul>

<div class="tab-content">
    <?php
    include "index-comments.php";
    include "index-users.php";
    include "index-config.php";
    ?>
</div>
