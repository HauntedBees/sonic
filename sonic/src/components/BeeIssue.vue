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
                    <v-icon @click="FeedbackForIssue" color="red darken-2" style="padding-top:1px" v-bind="attrs" v-on="on">mdi-alert-box</v-icon>
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
            "item": { type: Object, required: true }
        },
        data: () => ({
            contentWarningByPassed: false
        }),
        inject: ["triggerFeedback"],
        methods: {
            GetCompanyRelation(item) {
                if(this.companyName === undefined) { return ""; }
                const rel = GetRelationString(this.companyName, item.entityName);
                return rel === "" ? "" : (rel + " - ");
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
    window.cachedRelations = {};
    function FindRelation(a, b, alreadyHit) {
        if(a === b) { return ""; }
        alreadyHit = alreadyHit || [];
        if(alreadyHit.indexOf(a) >= 0 || alreadyHit.indexOf(b) >= 0) { return ""; }
        if(window.companyRelations[a] === undefined) { return ""; } // probably shoudn't happen
        if(window.companyRelations[a][b] !== undefined) { return window.companyRelations[a][b]; }
        alreadyHit.push(a);
        for(const company in window.companyRelations[a]) {
            const match = FindRelation(company, b, alreadyHit);
            if(match !== "") {
                return window.companyRelations[a][company] + match;
            }
        }
        return "";
    }
    function GetRelationString(a, b) {
        const key = `${a}-${b}`;
        if(window.cachedRelations[key] !== undefined) {
            return window.cachedRelations[key];
        }
        const res = FindRelation(a, b);
        if(res === "") { 
            window.cachedRelations[key] = "";
            return "";
        }
        const allKeys = [...new Set(res)];
        let result = "";
        if(allKeys.length === 1) {
            switch(allKeys[0]) {
                case "c": result = "Child Company"; break;
                case "p": result = "Parent Company"; break;
                case "o": result = "Investor"; break;
                case "m": result = "Investment"; break;
                case "x": result = "Business Relationship"; break;
            }
        } else if(res.indexOf("x") >= 0) {
            result = "Business Relationship";
        } else if(res[0] === "m") {
            result = "Investment";
        } else if(allKeys.indexOf("m") >= 0 || allKeys.indexOf("o") >= 0) {
            result = "Related through Investments";
        } else if(allKeys.indexOf("x") >= 0) {
            result = "Related through Business Relationships";
        } else {
            result = "Related Company";
        }
        window.cachedRelations[key] = result;
        return result;
    }
</script>