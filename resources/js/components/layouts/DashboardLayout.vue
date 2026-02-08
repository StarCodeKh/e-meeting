<template>
    <div class="app-layout">
        <header class="app-header">
            <slot name="header" />
        </header>

        <main class="app-content p-4">
            <slot />
        </main>
    </div>
</template>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Khmer:wght@400;500;700&display=swap');

    :root {
        --bg: #f1f5f9;
        --primary: #3b82f6;
    }

    * {
        box-sizing: border-box;
    }

    /* Design Tokens */
    :host {
        --coral: #e76f51;
        --sky: #3498db;
        --bg-light: #f8fafc;
    }

    html, body {
        margin: 0;
        height: 100%;
        font-family: 'Noto Sans Khmer', sans-serif;
    }

    .app-layout {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        background: var(--bg);
    }

    /* Sticky Header */
    .app-header {
        position: sticky;
        top: 0;
        z-index: 50;
        background: white;
        border-bottom: 1px solid #e5e7eb;
    }

    /* Dynamic Content Area */
    .app-content {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
        scroll-behavior: smooth;
    }

    .khmer-font { font-family: 'Kantumruy Pro', sans-serif !important; }

</style>
