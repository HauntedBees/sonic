<template>
    <div style="display:inline-block">
        <v-tooltip top style="vertical-align:top">
            <template v-slot:activator="{on, attrs}">
                    <v-chip
                        v-bind="attrs"
                        v-on="on"
                        @click="showSources=!showSources"
                        dark
                        class="selectionchip"
                        style="margin:5px;cursor:pointer"
                        >
                        <v-icon :color="item.issueColor">mdi-{{item.issueIcon}}</v-icon>
                    </v-chip>
            </template>
            <span>{{item.issueType}}</span>
        </v-tooltip>
        <div v-show="showSources">
            <a v-for="(source, idx) in item.sources" :key="idx" :href="source" rel="external nofollow noopener noreferrer">[Source]</a>
        </div>
    </div>
</template>
<script>
    export default {
        props: {
            "item": { type: Object, required: true }
        },
        data: () => ({
            showSources: false
        })
    }
</script>