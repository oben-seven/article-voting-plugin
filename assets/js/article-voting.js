/**
 * Checks if the user has already voted for the given post.
 * If the user has already voted, it fetches the voting result from the server.
 * Otherwise, it handles the voting process.
 */
jQuery(document).ready(function($) {
    /**
     * Checks if the user has already voted for the given post.
     * @param {number} post_id - The ID of the post to check the voting state for.
     */
    function checkVotingState(post_id) {
        // Check localStorage
        var hasVoted = localStorage.getItem('voted_' + post_id);

        if (hasVoted) {
            // User has already voted, fetch the voting result from the server
            $.ajax({
                type: 'POST',
                url: article_voting_params.ajax_url,
                data: {
                    action: 'get_voting_percentage',
                    post_id: post_id,
                },
                success: function(response) {
                    if (response.success) {
                        var data = response.data;
                        var positive_percentage = data.positive_votes > 0 ? Math.round((data.positive_votes / data.total_votes) * 100) : 0;
                        var negative_percentage = data.negative_votes > 0 ? Math.round((data.negative_votes / data.total_votes) * 100) : 0;
                        console.log(data);
                        $('.article-voting button').prop('disabled', true);
                        $(`.article-voting`).append(`<div class="article-voting-result">`);
                        $(`.article-voting`).append(`<p class="result-text">THANK YOU FOR YOUR FEEDBACK.</p>`);
                        $(`.article-voting`).append(`<span class="vote-yes yes-result">&#128516; ${positive_percentage}%</span>`);
                        $(`.article-voting`).append(`<span class="vote-no no-result">&#128542; ${negative_percentage === 0 ? '0' : negative_percentage}%</span>`);
                        $(`.article-voting`).append(`</div>`);

                        // Add active class to the corresponding result element
                        var vote = sessionStorage.getItem('votedButton');
                        $(`.yes-result, .no-result`).removeClass('active');
                        $(`.${vote}-result`).addClass('active');

                    } else {
                        alert(response.data);
                    }
                },
                error: function(error) {
                    console.log(error);
                },
            });
        }
    }

    /**
     * Handles the voting process.
     * @param {number} post_id - The ID of the post to vote for.
     * @param {string} vote - The user's voting choice ('yes' or 'no').
     */
    function handleVoting(post_id, vote) {
        $.ajax({
            type: 'POST',
            url: article_voting_params.ajax_url,
            data: {
                action: 'save_vote',
                post_id: post_id,
                vote: vote,
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    console.log(data);
                    var totalVotes = data.positive_votes + data.negative_votes;
                    var percentage = totalVotes > 0 ? Math.round((data.positive_votes / totalVotes) * 100) : 0;
                    var negativepercentage = totalVotes > 0 ? Math.round((data.negative_votes / totalVotes) * 100) : 0;
                    $('.article-voting button').prop('disabled', true);
                    $(`.article-voting`).append(`<div class="article-voting-result">`);
                    $(`.article-voting`).append(`<p class="result-text">THANK YOU FOR YOUR FEEDBACK.</p>`);
                    $(`.article-voting`).append(`<span class="vote-yes yes-result">&#128516; ${percentage}%</span>`);
                    $(`.article-voting`).append(`<span class="vote-no no-result">&#128542; ${negativepercentage}%</span>`);
                    $(`.article-voting`).append(`</div>`);

                    // Add active class to the corresponding result element
                    $(`.yes-result, .no-result`).removeClass('active');
                    $(`.${vote}-result`).addClass('active');

                    // Store the voting state in localStorage
                    localStorage.setItem('voted_' + post_id, true);
                    // Store the voting data in sessionStorage
                    sessionStorage.setItem('votedButton', vote);

                } else {
                    alert(response.data);
                }
            },
            error: function(error) {
                console.log(error);
            },
        });
    }

    // Check if the user has already voted for the current post
    checkVotingState(article_voting_params.post_id);

    // Handle the voting process when the user clicks a button
    $('.article-voting button').on('click', function() {
        var $this = $(this);
        var vote = $this.hasClass('vote-yes') ? 'yes' : 'no';
        handleVoting(article_voting_params.post_id, vote);
    });

    // Add event listener to load the voting state when the page loads
    window.addEventListener('load', function() {
        const votedButton = sessionStorage.getItem('votedButton');

        if (votedButton) {
            const voteElement = document.querySelector(`.vote-${votedButton}`);
            if (voteElement) {
                voteElement.classList.add('active');
            }

            // Select the result elements and add or remove the 'active' class based on the user's voting choice
            const resultElements = document.querySelectorAll('.yes-result, .no-result');
            resultElements.forEach(element => {
                if (element.classList.contains(`${votedButton}-result`)) {
                    element.classList.add('active');
                } else {
                    element.classList.remove('active');
                }
            });
        }
    });
});