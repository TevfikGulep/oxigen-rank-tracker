<div class="wrap">
    <h1><?php esc_html_e('Keyword Rank Tracker', 'keyword-rank-tracker'); ?></h1>
    <form method="post" action="">
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Keyword', 'keyword-rank-tracker'); ?></th>
                <td><input type="text" name="keyword" value="" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Track Date', 'keyword-rank-tracker'); ?></th>
                <td><input type="date" name="track_date" value="<?php echo esc_attr(date('Y-m-d')); ?>" /></td>
            </tr>
        </table>
        <?php submit_button(__('Track Keyword', 'keyword-rank-tracker')); ?>
    </form>

    <h2><?php esc_html_e('Tracked Keywords', 'keyword-rank-tracker'); ?></h2>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('Keyword', 'keyword-rank-tracker'); ?></th>
                <th><?php esc_html_e('Rank', 'keyword-rank-tracker'); ?></th>
                <th><?php esc_html_e('Date', 'keyword-rank-tracker'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch and display tracked keywords from the database
            // Example: foreach ($tracked_keywords as $keyword) { ... }
            ?>
        </tbody>
    </table>
</div>