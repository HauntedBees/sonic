<template>
    <v-container>
        <v-row v-if="notFound">
            <v-col style="text-align:center">
                <div class="d-inline d-sm-none beeheader">{{$route.params.id}}</div>
                <div class="d-none d-sm-inline beeheader">{{$route.params.id}}</div>
            </v-col>
        </v-row>
        <v-row v-if="notFound">
            <v-col class="beesubmessage" style="max-width:640px;margin:0 auto;text-align:center">
                We don't have any information on this. If you have something you'd like to share, click 
                <span class="beelink" @click="GiveDetails()">here</span> to submit some feedback!
            </v-col>
        </v-row>
        <v-row v-if="entry !== null">
            <v-col style="text-align:center">
                <div class="d-inline d-sm-none beeheader">{{entry.name}}</div>
                <div class="d-none d-sm-inline beeheader">{{entry.name}}</div>
                <v-tooltip top>
                    <template v-slot:activator="{on, attrs}">
                        <a
                            v-bind="attrs"
                            v-on="on"
                            external
                            nofollow
                            noopener
                            noreferrer
                            :href="'https://duckduckgo.com/?iar=news&q=%22'+encodeURIComponent(entry.name) + '%22'"
                            target="_blank"
                            class="viewNews"
                            >
                            <v-icon color="#1976D2">mdi-newspaper-variant</v-icon>
                        </a>
                    </template>
                    <span>Search for News</span>
                </v-tooltip>
            </v-col>
        </v-row>
        <v-row v-if="entry !== null" style="text-align:center">
            <v-col v-if="entry.typename">
                <BeeSubheader text="Category" />
                <div class="beesubmessage">{{entry.typename}}</div>
            </v-col>
            <v-col v-if="entry.parents.length > 0">
                <BeeSubheader text="Owned by" />
                <div class="beesubmessage">
                    <span v-for="(item, key) in entry.parents" :key="key">
                        <span v-if="key !== 0">, </span>
                        <router-link :to="`/`+parentInfo[item].text">{{parentInfo[item].text}}</router-link>
                        <span v-if="item !== parentInfo[item].rootid">
                            (<router-link :to="`/`+parentInfo[item].rootname">{{parentInfo[item].rootname}}</router-link>)
                        </span>
                    </span>
                </div>
            </v-col>
            <v-col v-if="entry.children.length > 0">
                <PotentiallyBigList title="Parent of" :items="entry.children"/>
            </v-col>
        </v-row>
        <v-row v-if="showAdditional" style="text-align:center">
            <v-col v-show="$store.state.loading">
                <v-progress-circular dark color="#FFFFFF" size="64" width="4" indeterminate />
            </v-col>
            <v-col v-show="entry.investments.length > 0">
                <PotentiallyBigList title="Investing in" :items="entry.investments"/>
            </v-col>
            <v-col v-show="entry.investors.length > 0">
                <PotentiallyBigList title="Invested in by" :items="entry.investors"/>
            </v-col>
            <v-col v-show="entry.relationships.length > 0">
                <PotentiallyBigList title="Business relationship wuth" :items="entry.relationships"/>
            </v-col>
        </v-row>
        <v-row v-if="entry !== null">
            <v-col v-if="!showAdditional && entry.hasAddtlRelationships" class="beesubmessage beelink beebar" style="text-align:center" @click="ShowAdditionalData">
                <span>Show Additional Relationships</span>
            </v-col>
            <v-col class="beesubmessage beelink beebar" style="text-align:center">
                <span v-show="!showGraph && (entry.parents.length > 0 || entry.children.length > 0)" @click="ShowGraph">Show Graph</span>
                <span v-show="showGraph" @click="HideGraph">Hide Graph</span>
            </v-col>
        </v-row>
        <v-row v-if="entry !== null && showGraph">
            <v-col><GraphDisplay :ready="graphLoaded" :nodes="nodes" :links="links"/></v-col>
        </v-row>
        <!--<v-row v-if="entry.description">
            <v-col cols="12">
                {{entry.description}}
            </v-col>
        </v-row>-->
        <v-row v-if="entry !== null">
            <v-col cols="1"/>
            <v-col cols="10">
                <CompanyInfoPageIssueList
                    v-if="entry !== null"
                    :company-name="entry.name"
                    :company-id="entry.id"
                    />
            </v-col>
            <v-col cols="1"/>
        </v-row>
    </v-container>
</template>
<script>
    import CompanyInfoPageIssueList from "src/views/CompanyInfoPageIssueList";
    import { bee } from "src/utils/webmethod.js";
    export default {
        components: { CompanyInfoPageIssueList },
        inject: ["triggerFeedback"],
        data: () => ({
            error: "",
            entry: null,
            parentInfo: null,
            showGraph: false, 
            showFullChildren: false,
            nodes: [],
            links: [],
            graphLoaded: false,
            notFound: false,
            showAdditional: false
        }),
        computed: {
            compChildren() { return this.entry.children.slice(0, 2); }
        },
        beforeRouteEnter(to, from, next) {
            bee.get("FindCompany", to.params.id, data => {
                next(vm => {
                    vm.entry = data.result;
                    vm.parentInfo = data.parentVals;
                    vm.showFullChildren = false;
                    vm.notFound = false;
                });
            }, () => {
                next(vm => {
                    vm.error = "No record found."
                    vm.notFound = true;
                });
            }, error => {
                next(vm => vm.error = error);
            });
        },
        beforeRouteUpdate(to, from, next) {
            this.entry = null;
            this.error = 0;
            this.showGraph = false;
            this.showFullChildren = false;
            this.notFound = false;
            this.links = [];
            this.nodes = [];
            bee.get("FindCompany", to.params.id, data => {
                this.entry = data.result;
                this.parentInfo = data.parentVals;
                next();
            }, () => {
                this.error = "No record found.";
                this.notFound = true;
                next();
            }, error => {
                this.error = error;
                next();
            });
        },
        methods: {
            ShowAdditionalData() {
                this.entry.investments = [];
                this.entry.investors = [];
                this.entry.relationships = [];
                this.showAdditional = true;
                bee.get("GetAdditionalCompanyInfo", this.entry.id, data => {
                    this.entry.investments = data.investments.map(t => ({text: t}));
                    this.entry.investors = data.investors.map(t => ({text: t}));
                    this.entry.relationships = data.relationships.map(t => ({text: t}));
                    if(this.showGraph) {
                        this.HideGraph();
                        this.ShowGraph();
                    }
                });
            },
            HideGraph() {
                this.nodes = [];
                this.links = [];
                this.showGraph = false;
                this.graphLoaded = false;
            },
            ShowGraph() {
                this.showGraph = true;
                if(this.nodes.length > 0) { return; }
                bee.get("GetGraphData", [this.entry.id, this.showAdditional], data => {
                    const nodeRef = {};
                    this.nodes = [];
                    this.links = [];
                    const existingLinks = {};
                    data.family.forEach(info => {
                        if(nodeRef[info.parentId] === undefined) {
                            const node = {
                                id: info.parentId,
                                name: info.parentName,
                                label: (info.parentName.indexOf("The")===0?info.parentName[4]:info.parentName[0]),
                                selected: info.parentId === this.entry.id,
                                size: 0,
                                iconx: parseInt(info.parentx),
                                icony: parseInt(info.parenty)
                            };
                            this.nodes.push(node);
                            nodeRef[node.id] = node;
                        } else { nodeRef[info.parentId].size++; }
                        if(nodeRef[info.childId] === undefined) {
                            const node = {
                                id: info.childId,
                                name: info.childName,
                                label: (info.childName.indexOf("The")===0?info.childName[4]:info.childName[0]),
                                selected: info.childId === this.entry.id,
                                size: 0,
                                iconx: parseInt(info.childx),
                                icony: parseInt(info.childy)
                            };
                            this.nodes.push(node);
                            nodeRef[node.id] = node;
                        }
                        const linkKey = info.parentId + "." + info.childId;
                        if(existingLinks[linkKey] === undefined) { // to prevent issues for companies with multiple parents
                            existingLinks[linkKey] = true;
                            this.links.push({
                                source: info.parentId,
                                target: info.childId,
                                relationtype: info.relationtype,
                                asOfDate: info.asOfDate
                            });
                        }
                    });
                    this.graphLoaded = true;
                });
            },
            GiveDetails() { this.triggerFeedback(-1); }
        }
    }
</script>