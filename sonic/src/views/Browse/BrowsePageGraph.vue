<template>
    <div>
        <GraphDisplay :ready="graphLoaded" :nodes="nodes" :links="links" :big="true" :cached="true" />
    </div>
</template>
<script>
    import { bee } from "src/utils/webmethod.js";
    export default {
        data: () => ({
            nodes: [],
            links: [],
            graphLoaded: false
        }),
        created() { this.LoadGraph() },
        methods: {
            LoadGraph() {
                bee.get("GetFullGraphDataFromCache", "", data => {
                    console.log(data);
                    this.nodes = data.nodes;
                    this.links = data.links;
                    this.graphLoaded = true;
                });
                /*bee.get("GetFullGraphData", "", data => {
                    this.nodes = data.nodes.map(n => ({
                        id: n.id,
                        name: n.name,
                        label: (n.name.indexOf("The")===0?n.name[4]:n.name[0]),
                        selected: false,
                        iconx: parseInt(n.iconx), 
                        icony: parseInt(n.icony),
                        size: data.links.filter(e => e.source === n.id).length
                    }));
                    this.links = data.links;
                    this.graphLoaded = true;
                });*/
            }
        }
    }
    window.GetGraphDataForCache = () => {
        console.log("STARTING:");
        if(window.iconImg === null) {
            window.iconImg = new Image();
            window.iconImg.src = require("src/assets/icons.png");
            window.iconImg.onload = window.GetGraphDataForCache;
            console.log("HOLD UP, LOADING THE IMAGE");
            return;
        }
        bee.get("GetFullGraphData", "", data => {
            try {
                console.log(data);
                const res = { success: true };
                res.nodes = data.nodes.map(n => ({
                    id: n.id,
                    name: n.name,
                    img: window.GetLogo(null, n.iconx, n.icony),
                    selected: false,
                    label: (n.name.indexOf("The")===0?n.name[4]:n.name[0]),
                    size: data.links.filter(e => e.source === n.id).length
                }));
                res.links = data.links.map(l => ({
                    source: l.source,
                    target: l.target,
                    relationtype: l.relationtype            
                }));
                console.log("JSON:");
                console.log(JSON.stringify(res));
            } catch(e) {
                console.log(e);
            }
        });
    }
</script>