<template>
    <div>
        <div style="width:1px;height:1px;overflow:hidden">
            <div class="renderGraph" style="width:1000px; height:1000px" />
        </div>
        <div class="graph loadedgraph" :style="{'width':big?'100%':'75%', 'height':big?'780px':'320px'}" />
        <div style="height:0px">
            <v-progress-circular v-show="!fullyLoaded" dark style="position:relative;left:47%;bottom:202px" color="#FFFFFF" size="64" width="4" indeterminate />
        </div>
        <div v-if="!big" style="text-align:right; margin: 0 12%">
            <v-btn @click="RenderMap" class="ma-2" dark color="blue darken-1">Download</v-btn>
        </div>
    </div>
</template>
<script>
    import cytoscape from "cytoscape";
    import popper from "cytoscape-popper";
    import tippy from "tippy.js";
    import { saveAs } from "file-saver";
    export default {
        props: {
            "ready": { type:Boolean, required: true },
            "nodes": { type:Array, required: true },
            "links": { type:Array, required: true },
            "big": { type:Boolean }
        },
        data: () => ({
            fullyLoaded: false,
            dataNodes: [],
            dataLinks: []
        }),
        watch: {
            ready() {
                this.fullyLoaded = false;
                if(!this.ready) { return; }
                this.BeginLoad();
            }
        },
        methods: {
            BeginLoad() {
                this.dataNodes = this.nodes.map(e => ({ data: e }));
                this.dataLinks = this.links.map(e => ({ data: e }));
                if(window.iconImg === null) {
                    window.iconImg = new Image();
                    window.iconImg.src = require("src/assets/icons.png");
                    window.iconImg.onload = () => this.InitGraph();
                } else {
                    this.InitGraph();
                }
            },
            InitGraph() {
                const me = performance.now();
                if(!window.alreadyInitialized) {
                    try {
                        cytoscape.use(popper);
                    } catch { // TODO: figure out a way to not reinitialize it every time
                        console.log("it did the thing bt we good");
                    }
                    window.alreadyInitialized = true;
                }

                const cy = this.GetCytoscapeObj(
                                document.getElementsByClassName("loadedgraph"),
                                { name: "cose", animate: false },
                                {
                                    "border-color": e => e.data("selected") ? "#22E546" : "#E7E721",
                                    "border-width": e => e.data("selected") ? "2px" : "3px",
                                    "color": "#000000",
                                    "font-size": "18px",
                                    "text-outline-color": "#115511",
                                    "text-valign": "center",
                                    "text-outline-opacity": 0.5,
                                    "text-margin-y": "1px",
                                    "content": e => isNaN(e.data("iconx")) ? e.data("label") : ""
                                }
                );
                cy.on("mouseover", "node", ShowTooltip);
                cy.on("tapstart", "node", ShowTooltip);
                cy.on("tapend", "node", ShowTooltip);
                cy.on("mouseout", "node", HideTooltip);
                cy.on("vclick", "node", e => {
                    document.getElementsByClassName("loadedgraph")[0].style["cursor"] = "default";
                    if(tip) { tip.destroy(); tip = null; }
                    const node = e.target;
                    const path = node.data("name");
                    if(`/${path}` === this.$route.path) { return; }
                    this.$router.push(`/${path}`);
                });
                console.log("Time: " + (performance.now() - me));
                this.fullyLoaded = true;
            },
            RenderMap() {
                const cy = this.GetCytoscapeObj(
                                document.getElementsByClassName("renderGraph"),
                                { name: "cose", animate: false, nodeDimensionsIncludeLabels: true },
                                {
                                    "border-color": "#E7E721",
                                    "border-width": "3px",
                                    "color": "#FFFFFF",
                                    "font-size": "18px",
                                    "text-outline-color": "#F90018",
                                    "text-valign": "bottom",
                                    "text-wrap": "wrap",
                                    "text-max-width": 160,
                                    "label": e => e.data("name")
                                }
                );
                const canv = document.createElement("canvas");
                canv.width = 1000;
                canv.height = 1000;
                const ctx = canv.getContext("2d");
                const graphImg = new Image();
                const date = new Date(Math.min(...this.links.map(e=>new Date(e.asOfDate))));
                graphImg.onload = () => {
                    ctx.drawImage(graphImg, 0, 0);
                    ctx.lineWidth = 1;
                    ctx.strokeStyle = "#F90018AA";
                    ctx.font = "24px Nevis";
                    ctx.fillStyle = "#FFFFFF";
                    const str = `The ${this.nodes[0].name} Family`;
                    ctx.strokeText(str, 6, 24);
                    ctx.fillText(str, 6, 24);
                    ctx.font = "16px Nevis";
                    ctx.textAlign = "right";
                    const credit = `Up to date as of ${months[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()} from https://hauntedbees.com/sonic.html`;
                    ctx.strokeText(credit, 996, 994);
                    ctx.fillText(credit, 996, 994);
                    canv.toBlob(b => {
                        saveAs(b, `${this.nodes[0].name} Graph.png`);
                    });
                };
                graphImg.src = cy.png({ bg: "#000048", maxWidth: 1000, maxHeight: 1000 });
            },
            GetCytoscapeObj(container, layout, nodeStyle) {
                const GetSize = e => {
                    const size = e.data("size");
                    return (30 + 5 * size) + "px";
                };
                const fullNodeStyle = Object.assign({
                    "background-image": GetLogo,
                    "background-width": "100%",
                    "background-height": "100%",
                    "width": GetSize,
                    "height": GetSize,
                    "border-style": "solid",
                    "border-opacity": 1,
                    "background-color": "#FFFFFF",
                    "font-family": "Nevis",
                    "text-halign": "center",
                    "text-outline-width": "1px"
                }, nodeStyle);
                return cytoscape({
                    container: container,
                    elements: {
                        nodes: this.dataNodes,
                        edges: this.dataLinks
                    },
                    layout: layout,
                    style: [
                        {
                            selector: "node[name]",
                            style: fullNodeStyle
                        }, {
                            selector: "edge",
                            style: {
                                "curve-style": "straight",
                                "target-arrow-shape": e => e.data("relationtype") === "3" ? "none" : "triangle",
                                "target-arrow-color": GetEdgeColor,
                                "line-color": GetEdgeColor,
                                "line-style": e => e.data("relationtype") === "3" ? "dashed" : "solid"
                            }
                        }
                    ]
                });
            }
        }
    }
    let tip = null;
    window.alreadyInitialized = false;
    const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    window.iconImg = null;
    window.savedLogos = {};
    function GetEdgeColor(e) {
        switch(e.data("relationtype")) {
            case "1": return "#E7E700";
            case "2": return "#00E513";
            case "3": return "#00A2E2";
        }
        return "#FFFFFF";
    }
    function GetLogo(e) {
        const iconx = e.data("iconx"), icony = e.data("icony");
        if(isNaN(iconx) || isNaN(icony)) { return "none"; }
        const coords = `${iconx},${icony}`;
        if(window.savedLogos[coords]) { return window.savedLogos[coords]; }
        const c = document.createElement("canvas");
        c.width = 32; c.height = 32;
        c.getContext("2d").drawImage(window.iconImg, 32 * iconx, 32 * icony, 32, 32, 0, 0, 32, 32);
        const uri = c.toDataURL("image/png");
        window.savedLogos[coords] = uri;
        return uri;
    }
    function ShowTooltip(e) {
        document.getElementsByClassName("loadedgraph")[0].style["cursor"] = "pointer";
        const node = e.target;
        const ref = node.popperRef();
        const dummyDomElement = document.createElement("div");
        if(tip) { tip.destroy(); }
        tip = new tippy(dummyDomElement, {
            trigger: "manual",
            lazy: false,
            onCreate: i => i.popperInstance.reference = ref,
            content: () => `
                <span class="beesubheader" style="margin-left: 10px; white-space: nowrap">
                    <span>${node.data("name")}</span>
                    <i/>
                </span>`
        });
        tip.show();
    }
    function HideTooltip() {
        document.getElementsByClassName("loadedgraph")[0].style["cursor"] = "default";
        if(tip) {
            tip.destroy();
            tip = null;
        }
    }
</script>