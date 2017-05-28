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

                    $submitFaceModal.on('hidden.bs.modal', function (e) {;
                        $submitFaceModalSubmitButton.prop('onclick', null).off('click');
                    });

                    $submitFaceModalSubmitButton.on('click', function() {
                        $submitFaceModalSubmitButton.prop("disabled", true);

                        var submitIdolName = $submitFaceModalIdolNameInput.val();
                        var submitIdolGroupsText = $submitFaceModalGroupNameInput.val();
                        var submitIdolPicture = $sampleImage.attr('src');
                        var submitIdolFace = base64ImageData;

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
                                //'picture': submitIdolPicture,
                                'face': submitIdolFace,
                                'source': 'web-modal'
                            },
                            success: function () {
                                $submitFaceModal.modal('hide')
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

    $('.queue-delete').each(function () {
        var $queueDeleteButton = $(this);
        var $queueDeleteId = $queueDeleteButton.data('queue-id');

        $queueDeleteButton.on('click', function() {
            $.ajax({
                type: 'POST',
                url: '/api/v1/delete_subject.json', // TODO: routing
                data: {
                    'subject-id': $queueDeleteId
                },
                success: function () {
                    location.reload();
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                }
            });
        });
    });

    $('.queue-accept').each(function () {
        var $queueAcceptButton = $(this);
        var $queueAcceptId = $queueAcceptButton.data('queue-id');
        var $queueAcceptIdolNameInput = $('#queue-idol-name-' + $queueAcceptId);
        var $queueAcceptGroupNameInput = $('#queue-group-name-' + $queueAcceptId);

        $queueAcceptButton.on('click', function() {
            var $queueAcceptIdolName = $queueAcceptIdolNameInput.val();
            var $queueAcceptGroupsNameText = $queueAcceptGroupNameInput.val();
            var $queueAcceptGroups = [];

            $.each($queueAcceptGroupsNameText.split(','), function(){
                $queueAcceptGroups.push($.trim(this));
            });

            $.ajax({
                type: 'POST',
                url: '/api/v1/accept_subject.json', // TODO: routing
                data: {
                    'subject-id': $queueAcceptId,
                    'subject-name': $queueAcceptIdolName,
                    'subject-groups': $queueAcceptGroups
                },
                success: function () {
                    location.reload();
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                }
            });
        });
    });
});