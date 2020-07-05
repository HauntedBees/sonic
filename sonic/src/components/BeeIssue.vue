<template>
    <v-list-item class="issue" dark>
        <div class="d-block d-sm-none bg" :style="{ 'color': item.issueColor }" />
        <div class="d-none d-sm-block bg" :style="{ 'color': item.issueColor }" />
        <v-list-item-avatar>
            <v-tooltip top>
                <template v-slot:activator="{on, attrs}">
                    <v-icon v-bind="attrs" v-on="on">mdi-{{item.issueIcon}}</v-icon>
                </template>
                <span>{{item.issueType}}</span>
            </v-tooltip>
        </v-list-item-avatar>
        <v-list-item-content>
            <v-list-item-subtitle v-if="item.entityId !== companyId">
                {{GetCompanyRelation(item)}}<router-link :to="'/' + item.entityName">{{item.entityName}}</router-link>
            </v-list-item-subtitle>
            <v-list-item-title class="wrap" v-text="item.issue"/>
            <v-list-item-subtitle class="text-right">
                <span v-if="item.ongoing">
                    <strong>Ongoing</strong> since {{item.startdate | moment("from")}}
                </span>
                <span v-if="!item.ongoing">
                    {{item.startdate | moment("from")}}
                    <span v-if="!item.ongoing && item.enddate"> - {{item.enddate | moment("from")}}</span>
                </span>
            </v-list-item-subtitle>
        </v-list-item-content>
        <v-list-item-action>
            <v-tooltip top>
                <template v-slot:activator="{on, attrs}">
                    <a
                        target="_blank"
                        style="text-decoration:none"
                        :href = "item.sourceurl"
                        external
                        nofollow
                        noopener
                        noreferrer
                        >
                        <v-icon color="blue darken-2" v-bind="attrs" v-on="on">mdi-link-box-variant</v-icon>
                    </a>
                </template>
                <span>View Source</span>
            </v-tooltip>
        </v-list-item-action>
        <v-list-item-action>
            <v-tooltip top>
                <template v-slot:activator="{on, attrs}">
                    <v-icon @click="FeedbackForIssue" color="red darken-2" v-bind="attrs" v-on="on">mdi-alert-box</v-icon>
                </template>
                <span>Report Inaccuracy</span>
            </v-tooltip>
        </v-list-item-action>
    </v-list-item>
</template>
<script>
    export default {
        props: {
            "companyId": { type: Number, required: true },
            "companyName": { type: String, required: false },
            "namePaths": { type: Array, required: false },
            "item": { type: Object, required: true }
        },
        inject: ["triggerFeedback"],
        methods: {
            GetCompanyRelation(item) {
                if(this.namePaths) {
                    const validNamePaths = this.namePaths.filter(e => e.indexOf(this.companyName) >= 0 && e.indexOf(item.entityName) >= 0);
                    if(validNamePaths.length === 0) { return "Related Company - "; }
                    for(let i = 0; i < validNamePaths.length; i++) {
                        const names = validNamePaths[i].split("|");
                        const myIdx = names.indexOf(this.companyName);
                        const theirIdx = names.indexOf(item.entityName);
                        return myIdx < theirIdx ? "Subsidiary - " : "Parent Company - ";
                    }
                    return "Related Company - ";
                } else {
                    if(item.namepath === "") { return ""; }
                    const names = item.namepath.split("|");
                    const myIdx = names.indexOf(this.companyName);
                    if(myIdx < 0) { return "Related Company - "; }
                    const theirIdx = names.indexOf(item.entityName);
                    return myIdx < theirIdx ? "Subsidiary - " : "Parent Company - ";
                }
            },
            FeedbackForIssue() {
                this.triggerFeedback(this.item);
            }
        }
    }
</script>