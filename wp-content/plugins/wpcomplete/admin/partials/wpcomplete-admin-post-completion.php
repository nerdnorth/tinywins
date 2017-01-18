
<div class="wrap">

  <h1>Post Completion - <?php echo $post->post_title; ?></h1>

  <div class="tablenav top">
    <div class="tablenav-pages one-page">
      <span class="displaying-num"><?php echo count($user_completed); ?> / <?php echo count($total_users); ?> completed</span>
    </div>
    <br class="clear">
  </div>
 
  <h2 class='screen-reader-text'>Users list</h2>
  <table class="wp-list-table widefat fixed striped users">
  <thead>
    <tr>
      <th scope="col" id='name' class='manage-column column-name'>Student Email</th>
      <th scope="col" id='completable' class='manage-column column-completable'>Completed</th>
    </tr>
  </thead>

  <tbody id="the-list" data-wp-lists='list:users'>
    <?php foreach ($total_users as $user) : ?>
    <tr id='user-<?php echo $user->ID; ?>'>
      <td class='name column-name' data-colname="Name"><a href="users.php?page=wpcomplete-users&amp;user_id=<?php echo $user->ID; ?>"><?php echo $user->user_email; ?></a></td>
      <td class='completable column-completable' data-colname="Completed">
        <div id="completable-<?php echo $user->ID; ?>">
          <?php if ( isset($user_completed[$user->ID]) ) : ?>
          <?php echo $user_completed[$user->ID]; ?>
          <?php else : ?>
          No
          <?php endif; ?>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>


</div>
