<?php

namespace Mohjak\WordPress\PostType\Metabox;

class MultipleAttachmentsMetabox
{
    private $screen;
    private $context;
    private $priority;
    private $mediaType;
    private $metaboxId;
    private $textDomain;
    private $metaboxNonce;
    private $metaboxTitle;
    private $metaboxPrefix;

    public function __construct($args = array())
    {
        $this->screen = isset($args['screen']) ? $args['screen'] : 'post';
        $this->context = isset($args['context']) ? $args['context'] : 'normal';
        $this->priority = isset($args['priority']) ? $args['priority'] : 'low';
        $this->mediaType = isset($args['mediaType']) ? $args['mediaType'] : '';
        $this->metaboxId = isset($args['metaboxPrefix']) ? $args['metaboxPrefix'] . '_metabox' : 'multiple_attachments_default_metabox';
        $this->textDomain = isset($args['textDomain']) ? $args['textDomain'] . '-multiple-attachments' : 'mohjak-multiple-attachments';
        $this->metaboxNonce = isset($args['metaboxPrefix']) ? $args['metaboxPrefix'] . '_metabox_nonce' : 'multiple_attachments_default_metabox_nonce';
        $this->metaboxTitle = isset($args['metaboxTitle']) ? $args['metaboxTitle'] : __('Upload multiple attachments', $this->textDomain);
        $this->metaboxPrefix = isset($args['metaboxPrefix']) ? $args['metaboxPrefix'] : 'multiple_attachments';

        add_action('add_meta_boxes', array($this, 'addAttachmentsMetabox'));
        add_action('save_post', array($this, 'saveAttachmentsMeta'));
        add_action('wp_ajax_deleteAttachment', array($this, 'deleteAttachment'));
    }

    public function addAttachmentsMetabox()
    {
        add_meta_box(
            $this->metaboxId, // $id
            $this->metaboxTitle, // $title
            array($this, 'showAttachmentsMetabox'), // $callback
            $this->screen, // $screen
            $this->context, // $context
            $this->priority // $priority
        );
    }

    public function showAttachmentsMetabox()
    {
        global $post;

        $attachments = explode(',', get_post_meta($post->ID, $this->metaboxPrefix, true));

        echo '<input type="hidden" name="' . $this->metaboxNonce . '" value="' . wp_create_nonce(basename(__FILE__)) . '">';
        echo '<div id="' . $this->metaboxPrefix . '_repeatable-fieldset" class="multiple-attachment-box">';
        echo '<style scoped>';
        echo '
        .multiple-attachment-wrapper {
            margin: 15px;
        }

        .row {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            width: 100%;
        }

        .column {
            display: flex;
            flex-direction: column;
            flex-basis: 100%;
            flex: 1;
            margin: 15px;
        }';
        echo '</style>';
        echo    '<div id="' . $this->metaboxPrefix . '_fields-wrapper" class="multiple-attachment-wrapper">';

        if ($attachments && is_array($attachments) && !empty($attachments[0]) && $attachments[0] !== '') {
            foreach ($attachments as $key => $att) {
                if ($att !== '') {
                    echo '<div id="' . $this->metaboxPrefix . '_row_' . $key . '" class="row ' . $this->metaboxPrefix . '_row" data-attachment-key="' . $att . '" data-row-key="' . $key . '">';
                    echo    '<div class="column">';
                    echo        '<input type="hidden" id="' . $this->metaboxPrefix . '_' . $key . '" name="' . $this->metaboxPrefix . '[]" class="attachment-input" value="' . $att . '">';
                    echo        '<input type="button" data-target="' . $this->metaboxPrefix . '_' . $key . '" data-row="' . $this->metaboxPrefix . '_row_' . $key . '" class="button button-secondary ' . $this->metaboxPrefix . '_button" value="' . __("Add attachement", $this->textDomain) . '" />';
                    echo    '</div>';
                    echo    '<div class="column">';
                    echo        '<input data-target="' . $this->metaboxPrefix . '_' . $key . '" data-row="' . $this->metaboxPrefix . '_row_' . $key . '" type="button" class="button button-secondary ' . $this->metaboxPrefix . '_remove" value="' . __("Remove attachment", $this->textDomain) . '" />';
                    echo    '</div>';
                    echo    '<div class="column">';
                    echo        '<div class="' . $this->metaboxPrefix . '-upload-wrapper">';
                    if ($att) {
                        $attachmentURL = wp_get_attachment_url($att);
                        if ($this->mediaType === 'image') {
                            echo '<a href="' . $attachmentURL . '" target="_blank"><img style="max-height: 180px;" src="' . $attachmentURL . '" /></a>';
                        } else {
                            echo '<a href="' . $attachmentURL . '" download>'.__('Download attachment here', $this->textDomain).'</a>';
                        }
                    }
                    echo        '</div>';
                    echo    '</div>';
                    echo    '<div class="column">';
                    echo        '<input type="button" class="button button-secondary remove-row remove-attachment-row" data-row="' . $this->metaboxPrefix . '_row_' . $key . '" value="'.__("Remove", $this->textDomain).'" />';
                    echo    '</div>';
                    echo '</div>';
                }
            }
        } else {
            echo '<div id="' . $this->metaboxPrefix . '_row_0" class="row ' . $this->metaboxPrefix . '_row" data-row-key="0" >';
            echo    '<div class="column">';
            echo        '<input class="attachment-input" type="hidden" id="' . $this->metaboxPrefix . '_0" />';
            echo        '<input type="button" data-target="' . $this->metaboxPrefix . '_0" class="button button-secondary ' . $this->metaboxPrefix . '_button" value="' . __("Add attachement", $this->textDomain) . '" />';
            echo    '</div>';
            echo    '<div class="column">';
            echo        '<input type="button" data-target="' . $this->metaboxPrefix . '_0" class="button button-secondary ' . $this->metaboxPrefix . '_remove" value="' . __("Remove attachment", $this->textDomain) . '" style="display: none;" />';
            echo    '</div>';
            echo    '<div class="column">';
            echo        '<div class="' . $this->metaboxPrefix . '-upload-wrapper">';

            echo        '</div>';
            echo    '</div>';
            echo    '<div class="column">';
            echo        '<input type="button" class="button button-secondary cmb-remove-row-button button-disabled" value="' . __("Remove", $this->textDomain) . '" />';
            echo    '</div>';
            echo '</div>';
        }
        echo '<div class="row"><div class="column"><input type="button" id="' .$this->metaboxPrefix. '_add-attachment-row" class="button" value="' . __("Add", $this->textDomain) . '" /></div></div>';
        echo '</div>';
        echo '</div>';

        echo '<div id="' . $this->metaboxPrefix . '_empty-attachment-row" class="row ' . $this->metaboxPrefix . '_row empty-attachment-row screen-reader-text" >';
        echo    '<div class="column">';
        echo        '<input type="hidden" class="attachment-input" />';
        echo        '<input type="button" class="button button-secondary ' . $this->metaboxPrefix . '_button" value="' . __("Add attachement", $this->textDomain) . '" />';
        echo    '</div>';
        echo    '<div class="column">';
        echo        '<input type="button" class="button button-secondary ' . $this->metaboxPrefix . '_remove" value="' . __("Remove attachment", $this->textDomain) . '" style="display:none;" />';
        echo    '</div>';
        echo    '<div class="column">';
        echo        '<div class="' . $this->metaboxPrefix . '-upload-wrapper">';
        echo        '</div>';
        echo    '</div>';
        echo    '<div class="column">';
        echo        '<input type="button" class="button button-secondary remove-attachment-row" value="' . __("Remove", $this->textDomain) . '" />';
        echo    '</div>';
        echo '</div>';
        ?>

        <script>
            var $ = jQuery.noConflict();
            function attachments_upload(button_class) {

                var mediaUploader;

                $(document.body).on("click", button_class, function(e) {
                    e.preventDefault();

                    $trigger = $(this);

                    if (mediaUploader) {
                        mediaUploader.open();
                        return;
                    }

                    mediaUploader = wp.media.frames.tgm_media_frame = wp.media({
                        className: "media-frame tgm-media-frame",
                        frame: "select",
                        multiple: false,
                        title: "<?php echo $this->metaboxTitle; ?>",
                        button: {
                            text: "<?php _e('Select', $this->textDomain); ?>"
                        },
                        library: {
                            type: "<?php echo $this->mediaType; ?>"
                        }
                    });

                    mediaUploader.on("select", function(e) {
                        var media_attachment = mediaUploader
                            .state()
                            .get("selection")
                            .first()
                            .toJSON();

                        var target = $trigger.data('target');
                        var $uploadInput = $(`#${target}`);
                        var $attachmentRow = $uploadInput.closest(".row");

                        var $uploadWrapper = $attachmentRow.find(
                            ".<?php echo $this->metaboxPrefix; ?>-upload-wrapper"
                        );

                        var $attachmentsRemove = $attachmentRow.find(
                            ".<?php echo $this->metaboxPrefix; ?>_remove"
                        );

                        $uploadInput.val(media_attachment.id);
                        $uploadInput.attr('name', "<?php echo $this->metaboxPrefix . '[]'; ?>");
                        $uploadInput.attr('id', `<?php echo $this->metaboxPrefix; ?>_${media_attachment.id}`);

                        $attachmentRow.attr('data-attachment-key', media_attachment.id);
                        if ($uploadInput.val() !== "") {
                            <?php if ($this->mediaType === 'image') { ?>
                                $uploadWrapper.html(
                                    `<a href="${media_attachment.url}" target="_blank"><img style="max-height: 180px;" src="${media_attachment.url}" /></a>`
                                );
                            <?php } else { ?>
                                $uploadWrapper.html(
                                    `<a class="<?php echo $this->metaboxPrefix; ?>-url" href="${media_attachment.url}"><?php _e('Download attachment here', $this->textDomain); ?></a>`
                                );
                            <?php } ?>

                            var $downloadLink = $uploadWrapper.find(
                                ".<?php echo $this->metaboxPrefix; ?>-url"
                            );

                            $downloadLink.css("display", "block");

                            $attachmentsRemove.css("display", "block");
                        }
                    });

                    mediaUploader.open();
                });
            }

            attachments_upload(
                ".<?php echo $this->metaboxPrefix; ?>_button.button"
            );

            $(document).ajaxComplete(function(event, xhr, settings) {
                if (settings.data) {
                    var queryStringArr = settings.data.split("&");
                    if ($.inArray("action=edit", queryStringArr) !== -1) {
                        var xml = xhr.responseXML;
                        $response = $(xml)
                            .find("post")
                            .text();
                        if ($response != "") {
                        }
                    }
                }
            });

            function isEmpty(el) {
                return !$.trim(el.html());
            }

            $(".<?php echo $this->metaboxPrefix; ?>_remove").on("click", function() {
                $this = $(this);
                var rowId = $this.data('row');

                var $attachmentRow = $this.closest(`#${rowId}`);

                var $uploadInput = $attachmentRow.find(
                    'input[name="<?php echo $this->metaboxPrefix; ?>[]"]'
                );

                var $uploadWrapper = $attachmentRow.find(
                    ".<?php echo $this->metaboxPrefix; ?>-upload-wrapper"
                );

                $uploadInput.val("");
                $uploadWrapper.html("");
                $this.hide();

                var post_id = $("#post_ID").val();
                var nonce = $('input[name="ajaxsecurity"]').val();
                $.ajax({
                    url: "admin-ajax.php",
                    type: "POST",
                    data: {
                        action: "deleteAttachment",
                        post_type: "post",
                        post_id: post_id,
                        ajaxsecurity: nonce
                    },
                    success: function(data) {
                        var parsed = JSON.parse(data);
                        if (parsed.deleted) {
                            console.log(`${parsed} attachment deleted.`);
                        }
                    }
                });
            });

            if ($("#<?php echo $this->metaboxPrefix; ?>_add-attachment-row")) {
                $("#<?php echo $this->metaboxPrefix; ?>_add-attachment-row").on("click", function() {
                    var metaBoxPrefix = '<?php echo $this->metaboxPrefix; ?>';

                    var $row = $(`#${metaBoxPrefix}_empty-attachment-row`).clone(true);
                    var $lastRow = $(`#${metaBoxPrefix}_repeatable-fieldset #${metaBoxPrefix}_fields-wrapper>.row.${metaBoxPrefix}_row:last`);
                    var lastRowKey = parseInt($lastRow.data('rowKey'));
                    var nextRowKey = lastRowKey + 1;
                    $row.attr('data-row-key', nextRowKey);
                    $row.attr('id', `${metaBoxPrefix}_row_${nextRowKey}`);

                    $row.find('.attachment-input').attr("id", `${metaBoxPrefix}_${nextRowKey}`);
                    $row.find(`.${metaBoxPrefix}_button.button`).attr("data-target", `${metaBoxPrefix}_${nextRowKey}`);
                    $row.find(`.${metaBoxPrefix}_remove.button`).attr("data-target", `${metaBoxPrefix}_${nextRowKey}`);

                    $row.removeClass("empty-attachment-row screen-reader-text");
                    if ($lastRow) {
                        $row.insertAfter($lastRow);
                    }

                    if ($(`#${metaBoxPrefix}_repeatable-fieldset #${metaBoxPrefix}_fields-wrapper>.row.${metaBoxPrefix}_row`).length == 0) { // TODO: check if rows container is empty
                        $(`#${metaBoxPrefix}_repeatable-fieldset #${metaBoxPrefix}_fields-wrapper`).prepend($row);
                    }

                    return false;
                });
            }

            if ($(".remove-attachment-row")) {
                $(".remove-attachment-row").on("click", function() {
                    var rowId = $(this).data("row");
                    if (rowId !== undefined) {
                        $(this)
                            .parents(`#${rowId}`)
                            .remove();
                    } else {
                        $(this)
                            .closest('.row')
                            .remove();
                    }

                    return false;
                });
            }
        </script>
    <?php }

    function saveAttachmentsMeta($post_id)
    {
        if (
            isset($_POST[$this->metaboxNonce])
            && !wp_verify_nonce($_POST[$this->metaboxNonce], basename(__FILE__))
        ) {
            return $post_id;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        if (isset($_POST['post_type'])) {
            if ('post' === $_POST['post_type']) {
                if (!current_user_can('edit_page', $post_id)) {
                    return $post_id;
                } elseif (!current_user_can('edit_post', $post_id)) {
                    return $post_id;
                }
            }
        }

        $attachments = get_post_meta($post_id, $this->metaboxPrefix, true);
        if (isset($_POST[$this->metaboxPrefix])) {
            $att = $_POST[$this->metaboxPrefix];

            $postedAttachments = implode(',', array_filter($att));

            if ($att && is_array($att) && $postedAttachments !== $attachments) {
                update_post_meta($post_id, $this->metaboxPrefix, $postedAttachments);
            } elseif ('' === $att && $attachments) {
                delete_post_meta($post_id, $this->metaboxPrefix, $attachments);
            }

            if ($att && is_string($att) && $att !== $attachments) {
                update_post_meta($post_id, $this->metaboxPrefix, $att);
            } elseif ('' === $att && $attachments) {
                delete_post_meta($post_id, $this->metaboxPrefix, $attachments);
            }
        }
    }

    function deleteAttachment()
    {
        $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : '';

        if (
            isset($_POST[$this->metaboxNonce])
            && !wp_verify_nonce($_POST[$this->metaboxNonce], basename(__FILE__))
        ) {
            return $post_id;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        if (isset($_POST['post_type'])) {
            if ('post' === $_POST['post_type']) {
                if (!current_user_can('edit_page', $post_id)) {
                    return $post_id;
                } elseif (!current_user_can('edit_post', $post_id)) {
                    return $post_id;
                }
            }
        }

        $attachment = get_post_meta($post_id, $this->metaboxPrefix, true);
        if (isset($attachment)) {
            update_post_meta($post_id, $this->metaboxPrefix, $attachment);
            $status = array(
                'attachment' => $attachment,
                'deleted' => true
            );
        } else {
            $status = array(
                'attachment' => $attachment,
                'deleted' => false
            );
        }
        die(json_encode($status));
    }
}
