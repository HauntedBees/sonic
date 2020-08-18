<template>
    <div>
        <div style="width:1px;height:1px;overflow:hidden">
            <div class="renderGraph" style="width:1000px; height:1000px" />
        </div>
        <div class="graph loadedgraph" :style="{'width':big?'100%':'75%', 'height':big?'780px':'320px'}" />
        <div style="height:0px">
            <v-progress-circular
                v-show="!fullyLoaded"
                dark
                :style="{position:'relative', left: '47%', bottom: big?'502px':'202px' }"
                color="#FFFFFF"
                size="64"
                width="4" 
                indeterminate />
        </div>
        <div class="beesubmessage beebar"
            v-show="big&&!fullyLoaded"
            style="text-align:center; bottom: 602px">
            {{$t("bigGraphLoader")}}
        </div>
        <div v-if="!big" style="text-align:right; margin: 0 12%">
            <v-btn @click="GenerateGraphImageFile" class="ma-2" dark color="blue darken-1">Download</v-btn>
        </div>
    </div>
</template>
<script>
    import cytoscape from "cytoscape";
    import fcose from "cytoscape-fcose";
    import popper from "cytoscape-popper";
    import tippy from "tippy.js";
    import { saveAs } from "file-saver";
    export default {
        props: {
            "ready": { type:Boolean, required: true },
            "nodes": { type:Array, required: true },
            "links": { type:Array, required: true },
            "big": { type:Boolean },
            "cached": { type:Boolean }
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
                window.InitializeLogoImages(this.InitGraph);
            },
            InitGraph() {
                const me = performance.now();
                if(!window.alreadyInitialized) {
                    try {
                        cytoscape.use(popper);
                        cytoscape.use(fcose);
                    } catch { // TODO: figure out a way to not reinitialize it every time
                        console.log("it did the thing bt we good");
                    }
                    window.alreadyInitialized = true;
                }

                const cy = this.GetCytoscapeObj(
                                document.getElementsByClassName("loadedgraph"),
                                (
                                    this.big
                                    ?
                                    {
                                        name: "fcose",
                                        animate: false,
                                        fit: true, 
                                        packComponents: false,
                                        nodeDimensionsIncludeLabels: true,
                                        nodeRepulsion: 10000,
                                        nodeSeparation: 100,
                                        nestingFactor: 4,
                                        edgeElasticity: 0.1,
                                        initialEnergyOnIncremental: 7,
                                        gravity: 10,
                                        gravityRangeCompound: 0.5,
                                        quality: "proof"
                                    }
                                    :
                                    { name: "cose", animate: false, fit: true, quality: "proof", nodeDimensionsIncludeLabels: true }
                                ),
                                {
                                    "border-color": e => e.data("selected") ? "#22E546" : "#E7E721",
                                    "border-width": e => e.data("selected") ? "2px" : "3px",
                                    "color": "#000000",
                                    "font-size": "18px",
                                    "text-outline-color": "#FFFFFF",
                                    "text-valign": "center",
                                    "text-outline-opacity": 0.5,
                                    "text-margin-y": "1px",
                                    "content": e => isNaN(e.data("iconx")) && e.data("img") === "none" ? e.data("label") : ""
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
            GenerateGraphImageFile() {
                const cy = this.GetCytoscapeObj(
                                document.getElementsByClassName("renderGraph"),
                                { name: "cose", animate: false, fit: true, nodeDimensionsIncludeLabels: true, quality: "proof" },
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
                const bgImgFunc = this.cached ? e => e.data("img") : window.GetLogo;
                const fullNodeStyle = Object.assign({
                    "background-image": bgImgFunc,
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
    window.logoImages = [];
    window.unloadedImages = -1;
    window.logoSplitterCtx = null;
    window.savedLogos = {};
    window.InitializeLogoImages = callback => {
        if(window.unloadedImages < 0) { // -1 when unloaded, 0 when loaded, >0 when loading
            window.unloadedImages = 2; // increase when adding new images
            const onloadCallback = () => {
                if(--window.unloadedImages === 0) {
                    callback();
                }
            };
            // TODO: webpack doesn't allow dynamic requires
            for(let i = 0; i < window.unloadedImages; i++) {
                const img = new Image();
                switch(i) {
                    case 0: img.src = require("src/assets/icons.png"); break;
                    case 1: img.src = require("src/assets/icons2.png"); break;
                }
                img.onload = onloadCallback;
                window.logoImages.push(img);
            }
        } else {
            callback();
        }
    };
    

    function GetEdgeColor(e) {
        switch(e.data("relationtype")) {
            case "1": return "#E7E700";
            case "2": return "#00E513";
            case "3": return "#00A2E2";
        }
        return "#FFFFFF";
    }
    window.GetLogo = (e, x, y, img) => {
        const iconx = e === null ? x : e.data("iconx"), icony = e === null ? y : e.data("icony");
        const imageIdx = img !== undefined ? img : e.data("img");
        if(isNaN(iconx) || isNaN(icony) || iconx === null || icony === null) { return "none"; }
        const coords = `${iconx},${icony}`;
        if(window.savedLogos[coords]) { return window.savedLogos[coords]; }
        if(window.logoSplitterCtx === null) {
            const c = document.createElement("canvas");
            c.width = 32; c.height = 32;
            window.logoSplitterCtx = c.getContext("2d");
        } else {
            window.logoSplitterCtx.clearRect(0, 0, 32, 32);
        }
        window.logoSplitterCtx.drawImage(window.logoImages[imageIdx], 32 * iconx, 32 * icony, 32, 32, 0, 0, 32, 32);
        const uri = window.logoSplitterCtx.canvas.toDataURL("image/png");
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