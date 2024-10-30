<div id="im4wp-list-fetcher">
    <form method="post" action="">
        <p>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=iys-panel-wp-form-forms' ) ); ?>" value="<?php echo esc_html__( 'Renew Form Connections', 'iys-panel-wp-form' ); ?>" class="button" >
                <?php echo esc_html__( 'Renew Form Connections', 'iys-panel-wp-form' ); ?>
            </a>
        </p>
    </form>
</div>
<p><?php echo esc_html__( 'If you have added new brands to IYS Panel, new groups and originators to İletişim Makinesi, you can pull these items that you have just added to the IYS Panel WP Form by clicking this button. So you can associate your forms with these newly added brands, groups and sender names.', 'iys-panel-wp-form' ); ?></p>

<hr />

<p><?php echo esc_html__( 'Create and manage forms for your website.', 'iys-panel-wp-form' ); ?></p>

<div class="im4wp-lists-overview">
    <?php
    $forms = im4wp_get_forms(
        array(
            'orderby' => 'ID',
            'order' => 'ASC',
        )
    );

    if ( empty( $forms ) ) {
    ?>
        <p><?php echo esc_html__( 'No forms were found.', 'iys-panel-wp-form' ); ?>.</p>
    <?php
    } else {
        echo '<table class="widefat striped" id="im4wp-iyspanel-lists-overview">';

        $headings = array(
            esc_html__( 'ID', 'iys-panel-wp-form' ),
            esc_html__( 'Form Name', 'iys-panel-wp-form' ),
            esc_html__( 'Description', 'iys-panel-wp-form' ),
            esc_html__( 'İletişim Makinesi Group', 'iys-panel-wp-form' ),
            esc_html__( 'İYS Panel Brand', 'iys-panel-wp-form' ),
            esc_html__( 'IM ID', 'iys-panel-wp-form' ),
        );

        echo '<thead>';
        echo '<tr>';
        foreach ( $headings as $heading ) {
            echo sprintf( '<th>%s</th>', $heading );
        }
        echo '</tr>';
        echo '</thead>';

        foreach ( $forms as $form ) {
            echo '<tr>';
            echo sprintf( '<td><code>%s</code></td>', esc_html( $form->ID ) );
            echo sprintf( '<td><a href="' . admin_url( sprintf( 'admin.php?page=iys-panel-wp-form-forms&view=edit-form&form_id=%d', $form->ID ) ) . '">%s</a><span class="row-actions alignright"></span></td>', esc_html( $form->name ) );
            /* echo sprintf( '<td>%s</td>', esc_html( $list->stats->member_count ) ); */
            echo sprintf( '<td>%s</td>', esc_html( $form->description ) );
            if (isset($form->settings['lists'])) {
                echo sprintf( '<td><ul style="margin: 0px; list-style: initial;">');
                foreach ($form->settings['lists'] as $groupId) {
                    $group = $iyspanel->get_list($groupId);
                    $group_name = $group->name;
                    echo sprintf( '<li>%s</li>', esc_html( $group_name ));
                }
                echo sprintf( '</ul></td>');
            } else {
                echo sprintf( '<td>-</td>' );
            }

            if (isset($form->settings['brands'])) {
                echo sprintf( '<td><ul style="margin: 0px; list-style: initial;">');
                foreach ($form->settings['brands'] as $brandId) {
                    $brand = $iyspanel->get_brand($brandId);
                    $brand_name = $brand->name;

                    echo sprintf( '<li>%s</li>', esc_html( $brand_name ));
                }
                echo sprintf( '</ul></td>');
            } else {
                echo sprintf( '<td>-</td>' );
            }
            
            echo sprintf( '<td>%s</td>', esc_html( $form->settings['wordpressId'] ) );
            echo '</tr>';
    ?>
    <?php
	} // end foreach $lists
	echo '</table>';
    } // end if empty
    ?>
</div>
