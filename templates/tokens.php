<div class="crumble-profile">
  <div class="crumble-profile-left">
    <?php if (!empty($contact->profileSource)): ?>
      <img src="<?php echo $client->Entity('Transfer')->url($contact->profileSource); ?>">
    <?php else: ?>
    <?php endif ?>
  </div>
  <div class="crumble-profile-body">
    <?php printf(__('Hi %s', 'crumble'), $contact->name); ?><br>
    <span class="crumble-smaller">
      <a class="crumble-logout" href="javascript:;" data-trigger="crumble_logout" data-success-view="login"><?php  _e('Logout', 'crumble') ?></a>
    </span>
  </div>
</div>


<?php if (empty($tokens)): ?>
<div class="crumble-info">
  <p><?php esc_html_e('There is no content shared with you', 'crumble' ); ?></p>
</div>
<?php else: ?>
  <p><?php esc_html_e('These are the files for you to download:', 'crumble' ); ?></p>

  <?php
  foreach ($tokens as $key => $token):
    if ($token->deleted):
      continue;
    endif;
  ?>

  <div class="crumble-shares">
    <div class="crumble-shares-left">
      <?php if ($token->entity->contentType == "@directory"): ?>
        <i class="fa fa-folder fa-2x"></i>
      <?php else: ?>
        <i class="fa fa-file-o fa-2x"></i>
      <?php endif; ?>
    </div>

    <div class="shares-entity">
      <a href="<?php echo $client->Entity('File')->download([$token->entity->id]); ?>" target="_blank">
        <?php echo $token->entity->name ?>
      </a>
      <br>

      <span class="crumble-smaller">
        <?php printf(__('by %s %s ago', 'crumble'),
            isset($token->entity->userReference->user) ? $token->entity->userReference->user->name : $token->entity->userReference->name,
            human_time_diff($token->entity->dateModified / 1000)); ?></span>
    </div>
  </div>

  <?php endforeach;?>
<?php endif; ?>