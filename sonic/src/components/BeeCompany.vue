<template>
    <v-list-item class="issue" dark :to="'/' + item.name">
        <div class="d-block d-sm-none bg" style="color:#AAAAAA" />
        <div class="d-none d-sm-block bg" style="color:#AAAAAA" />
        <v-list-item-avatar>
            <v-tooltip top>
                <template v-slot:activator="{on, attrs}">
                    <v-icon v-bind="attrs" v-on="on">mdi-{{item.categoryicon}}</v-icon>
                </template>
                <span>{{item.categoryname}}</span>
            </v-tooltip>
        </v-list-item-avatar>
        <v-list-item-content>
            <v-list-item-subtitle v-text="item.categoryname" />
            <v-list-item-title class="wrap" v-text="item.name"/>
            <v-list-item-subtitle class="text-right">
                <span v-if="item.issues > 0">
                    <strong>{{$t("issues")}}: </strong> {{item.issues}}
                </span>
                <span v-if="item.children > 0">
                    <strong>{{$t("subsidiaries")}}: </strong> {{item.children}}
                </span>
            </v-list-item-subtitle>
        </v-list-item-content>
    </v-list-item>
</template>
<script>
    export default {
        props: { "item": { type: Object, required: true } }
    }
</script>