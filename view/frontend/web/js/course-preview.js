define(['jquery', 'plyr'], function($) {
    $(document).ready(function () {
        const previewButton = $('#preview-course-btn');
        const previewModal = $('#preview-modal');
        const videoPlayer = $('#course-video-player')[0];
        const playlistItems = $('.playlist-item');

        // Open modal on button click
        previewButton.on('click', function () {
            previewModal.show();
            const firstVideoUrl = playlistItems.first().data('url');
            videoPlayer.src = firstVideoUrl;
            videoPlayer.play();
        });

        // Handle video switching in the playlist
        playlistItems.on('click', function () {
            const videoUrl = $(this).data('url');
            videoPlayer.src = videoUrl;
            videoPlayer.play();
        });

        // Initialize Plyr video player
        const player = new Plyr(videoPlayer);
    });
});
