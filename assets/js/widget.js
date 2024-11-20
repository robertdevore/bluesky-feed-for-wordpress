document.addEventListener("DOMContentLoaded", () => {
    const feedWidgets = document.querySelectorAll(".bluesky-feed-widget");

    // Check if any widgets are present.
    if (!feedWidgets.length) {
        console.warn("No Bluesky feed widgets found.");
        return;
    }

    // Process each widget.
    feedWidgets.forEach((feedWidget) => {
        const widgetSettings = feedWidget.dataset.settings
            ? JSON.parse(feedWidget.dataset.settings)
            : null;

        if (!widgetSettings) {
            console.warn("Widget settings missing for a Bluesky feed widget.");
            return;
        }

        // Log settings for debugging.
        console.log("Rendering widget with settings:", widgetSettings);

        // Render feed using shared function.
        renderFeed(feedWidget, widgetSettings);
    });
});
