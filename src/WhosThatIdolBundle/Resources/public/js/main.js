$(function () {
    var $sampleImage = $('.sample-image-container img');
    var $submitFaceModal = $('#submitFaceModal');

    if ($sampleImage.length) {
        $('.face-area').each(function () {
            var $faceArea = $(this);
            var img = new Image();
            img.onload = function () {
                $faceArea.on('click', function () {
                    var faceLeft = $faceArea.data('face-left');
                    var faceTop = $faceArea.data('face-top');
                    var faceWidth = $faceArea.data('face-width');
                    var faceHeight = $faceArea.data('face-height');
                    var groupName = $faceArea.data('group-name');
                    var idolName = $faceArea.data('idol-name');
                    var canvas = $('<canvas/>')
                        .attr({
                            width: faceWidth,
                            height: faceHeight
                        })
                        .hide()
                        .appendTo('body');
                    var ctx = canvas.get(0).getContext('2d');
                    //a = $('<a download="cropped-image" title="click to download the image" />'),
                    var cropCoords = {
                        topLeft: {
                            x: faceLeft,
                            y: faceTop
                        },
                        bottomRight: {
                            x: faceWidth,
                            y: faceHeight
                        }
                    };
                    ctx.drawImage(img, faceLeft, faceTop, faceWidth, faceHeight, 0, 0, faceWidth, faceHeight);//, 0, 0, img.width, img.height);

                    var base64ImageData = canvas.get(0).toDataURL();
                    canvas.remove();

                    $submitFaceModal.modal('show');
                    var $submitFaceModalImg = $submitFaceModal.find('img');
                    var $submitFaceModalIdolNameInput = $submitFaceModal.find('#face-idol-name');
                    var $submitFaceModalGroupNameInput = $submitFaceModal.find('#face-group-name');
                    var $submitFaceModalSubmitButton = $submitFaceModal.find('#submitFaceModalSubmitButton');

                    $submitFaceModalImg.attr('src', base64ImageData);
                    $submitFaceModalIdolNameInput.val(idolName);
                    $submitFaceModalGroupNameInput.val(groupName);

                    $submitFaceModalSubmitButton.on('click', function() {
                        $submitFaceModalSubmitButton.prop("disabled", true);

                        var submitIdolName = $submitFaceModalIdolNameInput.val();
                        var submitIdolGroupsText = $submitFaceModalGroupNameInput.val();
                        var submitIdolPicture = $sampleImage.attr('src');
                        var submitIdolFace = img.src;

                        var submitIdolGroups = [];

                        $.each(submitIdolGroupsText.split(','), function(){
                            submitIdolGroups.push($.trim(this));
                        });

                        $.ajax({
                            type: 'POST',
                            url: '/api/v1/new_subject.json', // TODO: routing
                            data: {
                                'name': submitIdolName,
                                'groups': submitIdolGroups,
                                'picture': submitIdolPicture,
                                'face': submitIdolFace,
                                'source': 'web-modal'
                            },
                            success: function () {
                                $submitFaceModal.modal('hide');
                            },
                            error: function (jqXHR) {
                                console.error(jqXHR);
                                //$submitFaceModal.modal();
                            },
                            complete: function() {
                                $submitFaceModalSubmitButton.prop("disabled", false);
                            }
                        });
                    });
                });
            };
            img.src = $sampleImage.attr('src');
        });
    }
});