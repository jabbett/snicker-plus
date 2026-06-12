<?php
/*
 |  Snicker Plus - A FlatFile Comment Plugin for Bludit
 |  @file       ./admin/index-comments.php
 |  @author     Steve Harris (Harris Lineage)
 |  @version    1.0.0
 |  @website    https://github.com/harrislineage/snicker-plus
 |  @license    MIT License
 |  @copyright  Copyright © 2025 Steve Harris (Harris Lineage)
 */

if (!defined('BLUDIT')) {
    die('Access denied');
}

global $pages, $security, $Snicker, $SnickerIndex, $SnickerPlugin, $SnickerUsers;

// Get Data
$limit = $SnickerPlugin->getValue("frontend_per_page");
if ($limit === 0) {
    $limit = 15;
}
$current = isset($current) ? $current : (isset($_GET["tab"]) ? $_GET["tab"] : "pending");
$commentSearch = isset($_GET["commentSearch"]) ? trim($_GET["commentSearch"]) : "";

// Get View
$view = "index";
if (isset($_GET["view"]) && in_array($_GET["view"], array("single", "uuid", "user"))) {
    $view = $current = $_GET["view"];
    $tabs = array($view);
} else {
    $tabs = array("pending", "approved", "rejected", "spam");
}

// Render Comemnts Tab
foreach ($tabs as $status) {
    if (isset($_GET["tab"]) && $_GET["tab"] === $status) {
        $page = max((isset($_GET["page"]) ? (int) $_GET["page"] : 1), 1);
    } else {
        $page = 1;
    }

    // Get Comments
    $isFiltered = ($view === "index" && $current === $status && $commentSearch !== "");
    if ($isFiltered) {
        $comments = $SnickerIndex->searchComments($commentSearch, $page, $limit, $status);
        $total = count($SnickerIndex->searchComments($commentSearch, 1, -1, $status));
    } else if ($view === "index") {
        $comments = $SnickerIndex->getList($status, $page, $limit);
        $total = $SnickerIndex->count($status);
    } else if ($view === "single") {
        $comments = $SnickerIndex->getListByParent(isset($_GET["single"]) ? $_GET["single"] : "");
        $total = count($comments);
    } else if ($view === "uuid") {
        $comments = $SnickerIndex->getListByUUID(isset($_GET["uuid"]) ? $_GET["uuid"] : "");
        $total = count($comments);
    } else if ($view === "user") {
        $comments = $SnickerIndex->getListByUser(isset($_GET["user"]) ? $_GET["user"] : "");
        $total = count($comments);
    }

    // Render Tab Content
    $link = DOMAIN_ADMIN . "snicker?page=%d&tab={$status}";
    if ($isFiltered) {
        $link .= "&commentSearch=" . urlencode($commentSearch);
    }
    ?>
    <div id="snicker-<?php echo $status; ?>" class="tab-pane <?php echo ($current === $status) ? "show active" : ""; ?>" role="tabpanel">
        <div class="snicker-search-row d-flex align-items-center flex-wrap mt-4 mb-4">
            <form class="form-inline flex-nowrap mr-3 mb-2" method="get" action="<?php echo DOMAIN_ADMIN; ?>snicker">
                <input type="hidden" name="tab" value="<?php echo $status; ?>" />
                <input type="text" name="commentSearch" value="<?php echo ($current === $status) ? Sanitize::html($commentSearch) : ""; ?>" class="snicker-search-control form-control mr-2" placeholder="<?php sn_e("Comment Title or Excerpt"); ?>" />
                <?php if ($isFiltered) { ?>
                    <a href="<?php echo DOMAIN_ADMIN; ?>snicker?tab=<?php echo $status; ?>" class="snicker-search-button btn btn-secondary mr-2" title="<?php sn_e("Clear"); ?>" aria-label="<?php sn_e("Clear"); ?>">
                        <span class="fa fa-times"></span>
                    </a>
                <?php } ?>
                <button class="snicker-search-button btn btn-primary" type="submit" title="<?php sn_e("Search Comments"); ?>" aria-label="<?php sn_e("Search Comments"); ?>">
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

        <?php /* No Comments available */ ?>
        <?php if (count($comments) < 1) { ?>
            <p class="mt-4 text-muted">
                <?php if ($isFiltered) { ?>
                    <?php sn_e("There are no %s comments matching \"%s\".", array(strtolower($strings[$status]), Sanitize::html($commentSearch))); ?>
                <?php } else { ?>
                    <?php sn_e("There are no %s comments at this moment.", array(strtolower($strings[$status]))); ?>
                <?php } ?>
            </p>
        </div>
        <?php continue; ?>
    <?php } ?>

    <?php /* Comments Table */ ?>
    <?php $link = DOMAIN_ADMIN . "snicker?action=snicker&snicker=%s&uid=%s&status=%s&tokenCSRF=" . $security->getTokenCSRF(); ?>
    <table class="table mt-3">
        <thead>
            <tr>
                <th class="border-0" scope="col"><?php sn_e("Comment"); ?></th>
                <th class="border-0 d-none d-lg-table-cell" scope="col"><?php sn_e("Author"); ?></th>
                <th class="border-0 text-center d-sm-table-cell" scope="col"><?php sn_e("Actions"); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($comments as $uid) { ?>
                <?php
                $data = $SnickerIndex->getComment($uid, $status);
                if (!(isset($data["page_uuid"]) && is_string($data["page_uuid"]))) {
                    continue;
                }
                $user = $SnickerUsers->getByString($data["author"]);
                ?>
                <tr>
                    <td class="pt-3">
                        <div>
                            <a style="font-size: 1.1em" href="<?php echo DOMAIN_ADMIN . "snicker/edit/?uid=" . $uid; ?>">
                                <?php
                                if ($SnickerPlugin->getValue("comment_title") !== "disabled" && !empty($data["title"])) {
                                    echo $data["title"];
                                } else {
                                    echo sn__("Comment");
                                }
                                ?>
                            </a>
                        </div>
                        <div>
                            <p style="font-size: 0.8em" class="m-0 text-muted">
                                <?php echo isset($data["excerpt"]) ? $data["excerpt"] : ""; ?>
                            </p>
                        </div>
                        <?php if (!empty($data["parent_uid"]) && $SnickerIndex->exists($data["parent_uid"]) && $view !== "single") { ?>
                            <?php
                            $reply = DOMAIN_ADMIN . "snicker?view=single&single={$uid}";
                            $reply = '<a href="' . $reply . '" title="' . sn__("Show all replies") . '">' . $SnickerIndex->getComment($data["parent_uid"])["title"] . '</a>';
                            ?>
                            <div class="text-muted mt-1" style="font-size: 0.8em;"><?php echo sn__("Reply To") . ": " . $reply; ?></div>
                        <?php } ?>
                    </td>
                    <td class="pt-3 d-none d-lg-table-cell">
                        <span class="d-inline-block"><?php echo $user["username"]; ?></span>
                        <p style="font-size: 0.8em" class="m-0 text-muted"><?php echo $user["email"]; ?></p>
                    </td>
                    <td class="contentTools pt-3 text-center d-sm-table-cell">
                        <?php $page = new Page($pages->getByUUID($data["page_uuid"])); ?>
                        <a class="text-secondary d-none d-md-inline" href="<?php echo $page->permalink(); ?>#comment-<?php echo $uid; ?>" target="_blank"><i class="fa fa-desktop"></i><?php sn_e("View"); ?></a>
                        <a class="text-secondary d-none d-md-inline ml-2" href="<?php echo DOMAIN_ADMIN . "snicker/edit/?uid=" . $uid; ?>"><i class="fa fa-edit"></i><?php sn_e("Edit"); ?></a>
                        <?php if ($status !== "approved") { ?>
                            <a class="text-secondary d-block d-lg-inline ml-lg-2" href="<?php printf($link, "moderate", $uid, "approved"); ?>"><i class="fa fa-check"></i><?php sn_e("Approve"); ?></a>
                        <?php } ?>
                        <?php if ($status !== "rejected") { ?>
                            <a class="text-secondary d-block d-lg-inline ml-lg-2" href="<?php printf($link, "moderate", $uid, "rejected"); ?>"><i class="fa fa-ban"></i><?php sn_e("Reject"); ?></a>
                        <?php } ?>
                        <?php if ($status !== "spam") { ?>
                            <a class="text-secondary d-block d-lg-inline ml-lg-2" href="<?php printf($link, "moderate", $uid, "spam"); ?>"><i class="fa fa-warning"></i><?php sn_e("Spam"); ?></a>
                        <?php } ?>
                        <?php if ($status !== "pending") { ?>
                            <a class="text-secondary d-block d-lg-inline ml-lg-2" href="<?php printf($link, "moderate", $uid, "pending"); ?>"><i class="fa fa-inbox"></i><?php sn_e("Pending"); ?></a>
                        <?php } ?>
                        <a class="text-danger d-block d-lg-inline ml-lg-2" href="<?php printf($link, "delete", $uid, "delete"); ?>"><i class="fa fa-trash"></i><?php sn_e("Delete"); ?></a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    </div>
    <?php
}
