<?php
/*
 |  Snicker Plus – A FlatFile Comment Plugin for Bludit
 |  @file       ./admin/index-users.php
 |  @author     Steve Harris (Harris Lineage)
 |  @version    1.0.0
 |  @website    https://github.com/harrislineage/snicker-plus
 |  @license    MIT
 |  @copyright  Copyright © 2025 Steve Harris (Harris Lineage)
 */

if (!defined('BLUDIT')) {
    exit('No direct access');
}
global $SnickerUsers;

// Get Data
$page = max((isset($_GET["page"]) ? (int) $_GET["page"] : 1), 1);
$limit = sn_config("frontend_per_page");
$total = count($SnickerUsers->db);
$search = "";
$isFiltered = (isset($current) && $current === "users" && isset($_GET["userSearch"]) && trim($_GET["userSearch"]) !== "");

// Get Users
if ($isFiltered) {
    $search = trim($_GET["userSearch"]);
    $total = count($SnickerUsers->getList($search, 1, -1));
}
$users = $SnickerUsers->getList($isFiltered ? $search : null, $page, $limit);

// Link
$link = DOMAIN_ADMIN . "snicker?page=%d&tab=users";
if ($isFiltered) {
    $link .= "&userSearch=" . urlencode($search);
}

?>
<div id="snicker-users" class="tab-pane <?php echo (isset($current) && $current === "users") ? "show active" : ""; ?>" role="tabpanel">
    <div class="snicker-search-row d-flex align-items-center flex-wrap mt-4 mb-4">
        <form class="form-inline flex-nowrap mr-3 mb-2" method="get" action="<?php echo DOMAIN_ADMIN; ?>snicker">
            <input type="hidden" name="tab" value="users" />
            <input type="text" name="userSearch" value="<?php echo Sanitize::html($search); ?>" class="snicker-search-control form-control mr-2" placeholder="<?php sn_e("Username or eMail Address"); ?>" />
            <?php if ($isFiltered) { ?>
                <a href="<?php echo DOMAIN_ADMIN; ?>snicker?tab=users" class="snicker-search-button btn btn-secondary mr-2" title="<?php sn_e("Clear"); ?>" aria-label="<?php sn_e("Clear"); ?>">
                    <span class="fa fa-times"></span>
                </a>
            <?php } ?>
            <button class="snicker-search-button btn btn-primary" type="submit" title="<?php sn_e("Search Users"); ?>" aria-label="<?php sn_e("Search Users"); ?>">
                <span class="fa fa-search"></span>
            </button>
        </form>

        <div class="ml-auto mb-2">
            <?php if ($limit !== -1 && $total > $limit) { ?>
                <div class="btn-group btn-group-pagination">
                    <?php if ($page <= 1) { ?>
                        <span class="btn btn-secondary disabled">&laquo;</span>
                        <span class="btn btn-secondary disabled">&lsaquo;</span>
                    <?php } else { ?>
                        <a href="<?php printf($link, 1); ?>" class="btn btn-secondary">&laquo;</a>
                        <a href="<?php printf($link, $page - 1); ?>" class="btn btn-secondary">&lsaquo;</a>
                    <?php } ?>
                    <?php if (($page * $limit) < $total) { ?>
                        <a href="<?php printf($link, $page + 1); ?>" class="btn btn-secondary">&rsaquo;</a>
                        <a href="<?php printf($link, ceil($total / $limit)); ?>" class="btn btn-secondary">&raquo;</a>
                    <?php } else { ?>
                        <span class="btn btn-secondary disabled">&rsaquo;</span>
                        <span class="btn btn-secondary disabled">&raquo;</span>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>

    <?php if (!$users || count($users) === 0) { ?>
        <?php if ($isFiltered) { ?>
            <p class="mt-4 text-muted"><?php sn_e("There are no users matching \"%s\".", array(Sanitize::html($search))); ?></p>
        <?php } else { ?>
            <p class="mt-4 text-muted"><?php sn_e("There are no users at this moment."); ?></p>
        <?php } ?>
    <?php } else { ?>
        <?php $link = DOMAIN_ADMIN . "snicker?action=snicker&snicker=users&uuid=%s&handle=%s&tokenCSRF=" . $security->getTokenCSRF(); ?>
        <table class="table mt-3">
            <thead>
                <tr>
                    <th class="border-0" scope="col"><?php sn_e("Username"); ?></th>
                    <th class="border-0 d-none d-lg-table-cell" scope="col"><?php sn_e("eMail"); ?></th>
                    <th class="border-0 text-center d-none d-md-table-cell" scope="col"><?php sn_e("Comments"); ?></th>
                    <th class="border-0 text-center d-sm-table-cell" scope="col"><?php sn_e("Actions"); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $uuid => $user) { ?>
                    <tr>
                        <td class="pt-3">
                            <div>
                                <a style="font-size: 1.1em" href="<?php echo DOMAIN_ADMIN; ?>snicker?view=user&user=<?php echo $uuid; ?>">
                                    <?php echo $user["username"]; ?>
                                </a>
                            </div>
                            <div class="d-lg-none">
                                <p style="font-size: 0.8em" class="m-0 text-muted"><?php echo $user["email"]; ?></p>
                            </div>
                        </td>
                        <td class="pt-3 d-none d-lg-table-cell">
                            <?php echo $user["email"]; ?>
                        </td>
                        <td class="pt-3 text-center d-none d-md-table-cell">
                            <a href="<?php echo DOMAIN_ADMIN; ?>snicker?view=user&user=<?php echo $uuid; ?>">
                                <?php echo count(isset($user["comments"]) ? $user["comments"] : array()); ?>
                                <?php sn_e("Comments"); ?>
                            </a>
                        </td>
                        <td class="contentTools pt-3 text-center d-sm-table-cell">
                            <a class="text-secondary d-none d-md-inline" href="<?php echo DOMAIN_ADMIN; ?>snicker?view=user&user=<?php echo $uuid; ?>"><i class="fa fa-comments"></i><?php sn_e("Comments"); ?></a>
                            <?php if ($user["blocked"]) { ?>
                                <a class="text-secondary d-block d-md-inline ml-md-2" href="<?php printf($link, $uuid, "unblock"); ?>"><i class="fa fa-unlock"></i><?php sn_e("Unblock"); ?></a>
                            <?php } else { ?>
                                <a class="text-secondary d-block d-md-inline ml-md-2" href="<?php printf($link, $uuid, "block"); ?>"><i class="fa fa-lock"></i><?php sn_e("Block"); ?></a>
                            <?php } ?>
                            <a class="text-secondary d-block d-md-inline ml-md-2" href="<?php printf($link, $uuid, "delete"); ?>&anonymize=true"><i class="fa fa-user-times"></i><?php sn_e("Anonymize"); ?></a>
                            <a class="text-danger d-block d-md-inline ml-md-2" href="<?php printf($link, $uuid, "delete"); ?>&anonymize=false"><i class="fa fa-trash-o"></i><?php sn_e("Delete"); ?></a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>
</div>
