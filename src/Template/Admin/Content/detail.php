<be-page-content>
    <div class="be-row">
        <div class="be-col-auto be-fw-bold">网址：</div>
        <div class="be-col">
            <?php echo $this->content->url; ?>
        </div>
    </div>

    <?php
    foreach ($this->content->fields as $field) {
        ?>
        <div class="be-mt-100 be-fw-bold">
            <?php echo $field['name']; ?>
        </div>

        <div class="be-mt-100 be-b-ccc be-p-100" style="overflow: hidden;">
        <?php echo $field['content']; ?>
        </div>
        <?php
    }
    ?>


    <div class="be-row be-mt-100">
        <div class="be-col-auto be-fw-bold">创建时间：</div>
        <div class="be-col">
            <?php echo $this->content->create_time; ?>
        </div>
    </div>

    <div class="be-row be-my-100">
        <div class="be-col-auto be-fw-bold">更新时间：</div>
        <div class="be-col">
            <?php echo $this->content->update_time; ?>
        </div>
    </div>

</be-page-content>