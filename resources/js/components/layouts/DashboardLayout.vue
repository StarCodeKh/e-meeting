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