document.addEventListener("DOMContentLoaded", () => {
    const feedPreview = document.getElementById("bluesky-feed-preview");

    // Make sure `renderFeed` is defined globally.
    renderFeed(feedPreview, blueskyAdminAjax);
});
