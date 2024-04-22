/**
 * Reset votes for a specific post when the reset-vote button is clicked.
 *
 * @param {jQuery.Event} event - The click event on the reset-vote button.
 */
jQuery(document).ready(function($) {
    // RESET VOTES FOR TESTING PURPOSE
    $('.reset-vote').on('click', function(event) {
        var postId = article_voting_params.post_id;

        $.ajax({
            /**
             * Reset the votes for a specific post using AJAX.
             *
             * @param {Object} options - The AJAX options.
             * @param {String} options.type - The HTTP request type (GET, POST, etc.).
             * @param {String} options.url - The URL to send the request to.
             * @param {Object} options.data - The data to send with the request.
             * @param {Function} options.success - The function to call if the request succeeds.
             * @param {Function} options.error - The function to call if the request fails.
             */
            type: 'POST',
            url: article_voting_params.ajax_url,
            data: {
                action: 'reset_vote',
                post_id: postId
            },
            success: function(response) {
                console.log(response.data);
                // Clear the localStorage data
                localStorage.removeItem('voted_' + postId);

                // Optionally, you can reset the UI elements here
                $('.article-voting button').prop('disabled', false);
                const resultEl = $('.article-voting-result');
                resultEl.remove();
                $('.voted-text, .result-text, .yes-result, .no-result').remove();
            },
            error: function(error) {
                console.log(error);
            }
        });
    });
});