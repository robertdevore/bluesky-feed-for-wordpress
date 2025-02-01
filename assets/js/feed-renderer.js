function renderFeed(container, settings) {
    const { username, postCount, includePins, includeLink, theme } = settings;

    if (!username) {
        container.innerHTML = "<p>Please enter a valid username above to see the feed.</p>";
        return;
    }

    // Apply the selected theme
    container.classList.remove("theme-light", "theme-dim", "theme-dark");
    container.classList.add(`theme-${theme}`);

    container.innerHTML = "<p>Loading feed...</p>";

    axios
        .get("https://public.api.bsky.app/xrpc/app.bsky.feed.getAuthorFeed", {
            params: {
                actor: username,
                limit: postCount,
                filter: "posts_no_replies",
                includePins: includePins === 1,
            },
        })
        .then((response) => {
            const { feed: posts } = response.data;

            container.innerHTML = "";

            if (!posts || posts.length === 0) {
                container.innerHTML = "<p>No posts found for this author.</p>";
                return;
            }

            posts.forEach((item) => {
                const post = item.post;

                // Skip pinned posts if includePins is false.
                if (!includePins && post.record?.pinned) {
                    return;
                }

                const authorName = post.author.displayName || post.author.handle;
                const authorAvatar = post.author.avatar || "https://bsky.social/default-avatar.png";
                const content = post.record?.text || "[No text content]";
                const createdAt = new Date(post.indexedAt).toLocaleString();

                const postUriSegments = post.uri.split("/");
                const postId = postUriSegments.pop();
                const handle = post.author.handle;
                const postUrl = `https://bsky.app/profile/${handle}/post/${postId}`;

                const replyCount = post.replyCount || 0;
                const repostCount = post.repostCount || 0;
                const likeCount = post.likeCount || 0;

                // Check if the post is a repost (retweet)
                let repostIndicator = "";
                if (item.reason && item.reason.$type === "app.bsky.feed.defs#reasonRepost") {
                    const originalAuthor = item.reason.by.displayName || item.reason.by.handle;
                    repostIndicator = `
                        <div class="bluesky-repost-indicator">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-repeat" width="18" height="18" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24V24H0z" fill="none"></path>
                                <path d="M8 9l-4 -4l4 -4"></path>
                                <path d="M16 15l4 4l-4 4"></path>
                                <path d="M4 5h11a4 4 0 0 1 4 4v4"></path>
                                <path d="M20 19h-11a4 4 0 0 1 -4 -4v-4"></path>
                            </svg>
                            <span>Reposted from <strong>${originalAuthor}</strong></span>
                        </div>
                    `;
                }

                // Extract media images (Force animated GIFs to load correctly)
                let mediaHtml = "";
                if (post.embed?.images) {
                    mediaHtml = `<div class="bluesky-images">`;
                    post.embed.images.forEach((image) => {
                        // Check if the image is a GIF
                        const isGif = image.fullsize.toLowerCase().includes(".gif");

                        // Remove query parameters from GIF URLs
                        let fullsizeUrl = image.fullsize;
                        if (isGif) {
                            fullsizeUrl = fullsizeUrl.split("?")[0]; // Remove ?hh=333&ww=498
                        }

                        mediaHtml += `
                            <img src="${isGif ? fullsizeUrl : image.thumb}" 
                                alt="Bluesky Post Image" 
                                class="bluesky-image">
                        `;
                    });
                    mediaHtml += `</div>`;
                }

                // Extract external link previews (cards) with large image
                let linkPreviewHtml = "";
                    if (post.embed?.external) {
                        const { uri, title, description, thumb } = post.embed.external;

                        // Check if the `uri` contains a `.gif`, and remove query parameters
                        const isGif = uri.toLowerCase().includes(".gif");
                        let gifUrl = uri.split("?")[0]; // Remove ?hh=333&ww=498 if present

                        linkPreviewHtml = `
                        <div class="bluesky-link-preview">
                            <a href="${uri}" target="_blank" rel="noopener noreferrer" class="bluesky-link-card">
                                ${
                                    isGif
                                        ? `<img src="${gifUrl}" alt="Animated GIF" class="bluesky-gif-image">`
                                        : `<img src="${thumb}" alt="Link preview image" class="bluesky-link-image-large">
                                            <div class="bluesky-link-info">
                                                <strong>${title}</strong>
                                                <p>${description}</p>
                                            </div>`
                                }
                            </a>
                        </div>
                    `;
                }

                // Format post content to preserve line breaks, clickable full URLs, and hashtags
                const formattedContent = content
                    .replace(/\n/g, "<br>") // Preserve newlines as <br>
                    .replace(/((https?:\/\/[^\s]+))/g, '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>') // Ensure full URLs remain clickable
                    .replace(/(^|\s)(#[a-zA-Z0-9_]+)/g, '$1<span class="bluesky-hashtag">$2</span>'); // Style hashtags

                const postElement = document.createElement("div");
                postElement.className = "bg-white dark:bg-gray-900 dim:bg-gray-800 p-4 rounded-md shadow-md mb-4";

                postElement.innerHTML = `
                    ${repostIndicator} <!-- Show repost indicator above the post -->
                    <a href="${postUrl}" target="_blank" rel="noopener noreferrer" class="bluesky-post-link">
                    <div class="bluesky-post-header">
                        <img src="${authorAvatar}" alt="${authorName}" class="bluesky-avatar">
                        <span class="bluesky-author">${authorName}</span>
                        </div>
                        <div class="text-gray-700 dark:text-gray-300 dim:text-gray-400 mt-2">${formattedContent}</div>
                        ${mediaHtml}
                        ${linkPreviewHtml}
                        <div class="text-gray-500 text-sm mt-2">${createdAt}</div>
                        ${
                            includeLink && postUrl
                                ? `</a>`
                                : ""
                        }
                        <div class="reaction-icons mt-4">
                            <div class="reaction-item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-message-circle" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24V24H0z" fill="none"></path>
                                    <path d="M3 21v-4a9 9 0 1 1 4 4h-4"></path>
                                </svg>
                                <span>${replyCount}</span>
                            </div>
                            <div class="reaction-item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-heart" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24V24H0z" fill="none"></path>
                                    <path d="M12 20l-7 -7a4 4 0 0 1 0 -5.6a4 4 0 0 1 5.6 0l1.4 1.4l1.4 -1.4a4 4 0 0 1 5.6 0a4 4 0 0 1 0 5.6z"></path>
                                </svg>
                                <span>${likeCount}</span>
                            </div>
                        </a>
                    </div>
                `;

                container.appendChild(postElement);
            });
        })
        .catch((error) => {
            console.error("Error fetching feed:", error);
            container.innerHTML =
                "<p>Failed to fetch feed. Please check the console for details.</p>";
        });
}

// Export function if needed in modern JS.
if (typeof module !== "undefined") {
    module.exports = { renderFeed };
}
