<style>
    .review_hotels{
        min-width: 500px;
        border:1px solid #ccc;
    }
    .review_hotels tr{
    }
    .review_hotels td, .review_hotels th{
        padding:10px;
        text-align: center;
        border-bottom:1px solid #ccc;
    }
    .review_hotels th{
        background: #aaa;
    }

</style>
<table class="review_hotels">
    <tr>
        <th></th>
        <th><?php echo esc_attr__('To add', '1day'); ?></th>
        <th><?php echo esc_attr__('To update', '1day'); ?></th>
        <th><?php echo esc_attr__('To delete', '1day'); ?></th>
    </tr>
    <tr>
        <td><?php echo esc_attr__('Hotels', '1day'); ?></td>
        <td>
            <?php
                if(!empty($review['hotels']['add'])){
                    echo count($review['hotels']['add']);
                }else{
                    echo 0;
                }
            ?>
        </td>
        <td>
            <?php
            if(!empty($review['hotels']['update'])){
                echo count($review['hotels']['update']);
            }else{
                echo 0;
            }
            ?>
        </td>
        <td>
            <?php
                if(!empty($review['hotels']['delete'])){
                    echo count($review['hotels']['delete']);
                }else{
                    echo 0;
                }
            ?>
        </td>
    </tr>
    <tr>
        <td><?php echo __('Rooms', '1day'); ?></td>
        <td>
            <?php
            if(!empty($review['rooms']['add'])){
                echo count($review['rooms']['add']);
            }else{
                echo 0;
            }
            ?>
        </td>
        <td>
            <?php
            if(!empty($review['rooms']['update'])){
                echo count($review['rooms']['update']);
            }else{
                echo 0;
            }
            ?>
        </td>
        <td>
            <?php
            if(!empty($review['rooms']['delete'])){
                echo count($review['rooms']['delete']);
            }else{
                echo 0;
            }
            ?>
        </td>
    </tr>
</table>