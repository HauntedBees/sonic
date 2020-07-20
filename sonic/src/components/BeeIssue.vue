<template>
    <v-list-item class="issue" dark>
        <div class="d-block d-sm-none bg" :style="{ 'color': item.issueColor }" />
        <div class="d-none d-sm-block bg" :style="{ 'color': item.issueColor }" />
        <v-list-item-avatar>
            <v-tooltip top>
                <template v-slot:activator="{on, attrs}">
                    <v-icon :color="BoostColor(item.issueColor, 50)" v-bind="attrs" v-on="on">mdi-{{item.issueIcon}}</v-icon>
                </template>
                <span>{{item.issueType}}</span>
            </v-tooltip>
        </v-list-item-avatar>
        <v-list-item-content>
            <v-list-item-subtitle v-if="item.entityId !== companyId">
                {{GetCompanyRelation(item)}}<router-link :to="'/' + item.entityName">{{item.entityName}}</router-link>
            </v-list-item-subtitle>
            <v-list-item-title class="wrap" :style="{'text-align': (item.contentwarning && !contentWarningByPassed) ? 'center' : '' }">
                <span 
                    @click="contentWarningByPassed=true"
                    v-show="item.contentwarning && !contentWarningByPassed"
                    style="display:inline-block; cursor:pointer; border: 1px solid #AAAAAA; padding:4px 8px; border-radius:8px">
                    <em>Content Warning:</em> {{item.contentwarning}}
                    <br/>
                    <span style="font-size:0.8rem">Click here to show the details.</span>
                </span>
                <span v-show="!item.contentwarning || contentWarningByPassed">{{item.issue}}</span>
            </v-list-item-title>
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
                        rel = "external nofollow noopener noreferrer"
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
        data: () => ({
            contentWarningByPassed: false
        }),
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
            FeedbackForIssue() { this.triggerFeedback(this.item); },
            BoostColor(color, amt) {
                let [r, g, b] = color.substr(1).match(/.{2}/g);
                [r, g, b] = [
                    Math.min(255, parseInt(r, 16) + amt).toString(16),
                    Math.min(255, parseInt(g, 16) + amt).toString(16), 
                    Math.min(255, parseInt(b, 16) + amt).toString(16)
                ];
                const [rr, gg, bb] = [
                    (r.length === 2 ? "" : "0") + r,
                    (g.length === 2 ? "" : "0") + g,
                    (b.length === 2 ? "" : "0") + b
                ];
                return `#${rr}${gg}${bb}`;
            }
        }
    }
</script>