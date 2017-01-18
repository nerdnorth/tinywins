
<div class="wrap">

  <h1>User Completion - <?php echo $user->user_email; ?></h1>

  <div class="tablenav top">
    <div class="tablenav-pages one-page">
      <span class="displaying-num"><?php echo count($user_completed); ?> / <?php echo count($total_posts); ?> completed</span>
    </div>
    <br class="clear">
  </div>
 
  <h2 class='screen-reader-text'>Posts list</h2>
  <table class="wp-list-table widefat fixed striped users">
  <thead>
    <tr>
      <th scope="col" id='title' class='manage-column column-title'>Title</th>
      <!--<th scope="col" id='course-name' class='manage-column column-course-name'>Course Name</th>-->
      <th scope="col" id='completable' class='manage-column column-completable'>Completed</th>
    </tr>
  </thead>

  <tbody id="the-list" data-wp-lists='list:posts'>
    <?php foreach ($total_posts as $post_id) : ?>
    <tr id='post-<?php echo $post_id; ?>'>
      <td class='name column-title' data-colname="Title"><a href="edit.php?page=wpcomplete-posts&amp;post_id=<?php echo $post_id; ?>"><?php echo get_the_title($post_id) . " (" . ucwords( str_replace( "_", " ", get_post_type( $post_id ) ) ) . " #" . $post_id . ")"; ?></a></td>
      <!--<td class="course-name column-course-name" data-colname="Course Name">
      </td>-->
      <td class='completable column-completable' data-colname="Completed">
        <div id="completable-<?php echo $post_id; ?>">
          <?php if ( isset($user_completed[$post_id]) ) : ?>
          <?php echo $user_completed[$post_id]; ?>
          <?php else : ?>
          No
          <?php endif; ?>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>


</div>
