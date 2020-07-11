<template>
    <div style="display:inline-block">
        <v-tooltip top style="vertical-align:top">
            <template v-slot:activator="{on, attrs}">
                <!--<a 
                    :href="item.sourceurl"
                    style="text-decoration:none"
                    external
                    nofollow
                    noopener
                    noreferrer
                    target="_blank">-->
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
                <!--</a>-->
            </template>
            <span>{{item.issueType}}</span>
        </v-tooltip>
        <div v-show="showSources">
            <a v-for="(source, idx) in item.sources" :key="idx" :href="source">[Source]</a>
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