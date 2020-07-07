<template>
    <div>
        <div style="width:1px;height:1px;overflow:hidden">
            <div class="renderGraph" style="width:1000px; height:1000px" />
        </div>
        <div class="graph loadedgraph" :style="{'width':big?'100%':'75%', 'height':big?'780px':'320px'}" />
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
            "nodes": { type:Array, required: true },
            "links": { type:Array, required: true },
            "big": { type:Boolean }
        },
        mounted() {
            iconImg.src = require("src/assets/icons.png");
            iconImg.onload = () => this.InitGraph();
        },
        methods: {
            InitGraph() {
                const me = performance.now();
                const GetSize = e => {
                    const size = e.data("size");
                    return (30 + 5 * size) + "px";
                };
                if(!alreadyInitialized) {
                    try {
                        cytoscape.use(popper);
                    } catch { // TODO: figure out a way to not reinitialize it every time
                        console.log("it did the thing bt we good");
                    }
                    alreadyInitialized = true;
                }

                const cy = cytoscape({
                    container: document.getElementsByClassName("loadedgraph"),
                    elements: {
                        nodes: this.nodes.map(e => ({ data: e })),
                        edges: this.links.map(e => ({ data: e }))
                    },
                    layout: { name: "cose", animate: false },
                    style: [
                        {
                            selector: "node[name]",
                            style: {
                                "content": e => isNaN(e.data("iconx")) ? e.data("label") : "",
                                "background-image": GetLogo,
                                "background-width": "100%",
                                "background-height": "100%",
                                "width": GetSize,
                                "height": GetSize,
                                "text-valign": "center",
                                "text-halign": "center",
                                "border-width": e => e.data("selected") ? "2px" : "3px",
                                "border-style": "solid",
                                "border-color": e => e.data("selected") ? "#22E546" : "#E7E721",
                                "border-opacity": 1,
                                "background-color": "#FFFFFF",
                                "font-family": "Nevis",
                                "font-size": "18px",
                                "color": "#000000",
                                "text-outline-color": "#115511",
                                "text-outline-width": "1px",
                                "text-outline-opacity": 0.5,
                                "text-margin-y": "1px"
                            }
                        }, {
                            selector: "edge",
                            style: {
                                "curve-style": "straight",
                                "target-arrow-shape": "triangle",
                                "target-arrow-color": "#E7E700",
                                "line-color": "#E7E700"
                            }
                        }
                    ]
                });
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
            },
            RenderMap() {
                const GetSize = e => {
                    const size = e.data("size");
                    return (30 + 5 * size) + "px";
                };
                const cy = cytoscape({
                    container: document.getElementsByClassName("renderGraph"),
                    elements: {
                        nodes: this.nodes.map(e => ({ data: e })),
                        edges: this.links.map(e => ({ data: e }))
                    },
                    layout: { name: "cose", animate: false },
                    style: [
                        {
                            selector: "node[name]",
                            style: {
                                "label": e => e.data("name"),
                                "text-valign": "bottom",
                                "text-halign": "center",
                                "text-wrap": "wrap",
                                "text-max-width": 160,
                                "font-size": "12px",
                                "background-image": GetLogo,
                                "background-width": "100%",
                                "background-height": "100%",
                                "width": GetSize,
                                "height": GetSize,
                                "border-width": "3px",
                                "border-style": "solid",
                                "border-color": "#E7E721",
                                "border-opacity": 1,
                                "background-color": "#FFFFFF",
                                "font-family": "Nevis",
                                "color": "#FFFFFF",
                                "text-outline-color": "#F90018",
                                "text-outline-width": "1px"
                            }
                        }, {
                            selector: "edge",
                            style: {
                                "curve-style": "straight",
                                "target-arrow-shape": "triangle",
                                "target-arrow-color": "#E7E700",
                                "line-color": "#E7E700"
                            }
                        }
                    ]
                });
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
                    const credit = `Up to date as of ${date.toLocaleDateString()} from https://hauntedbees.com/sonic.html`;
                    ctx.strokeText(credit, 996, 994);
                    ctx.fillText(credit, 996, 994);
                    canv.toBlob(b => {
                        saveAs(b, `${this.nodes[0].name} Graph.png`);
                    });
                };
                graphImg.src = cy.png({ bg: "#000048" });
            }
        }
    }
    let tip = null;
    let alreadyInitialized = false;
    const iconImg = new Image();
    const savedLogos = {};
    function GetLogo(e) {
        const iconx = e.data("iconx"), icony = e.data("icony");
        if(isNaN(iconx) || isNaN(icony)) { return "none"; }
        const coords = `${iconx},${icony}`;
        if(savedLogos[coords]) { return savedLogos[coords]; }
        const c = document.createElement("canvas");
        c.width = 32; c.height = 32;
        c.getContext("2d").drawImage(iconImg, 32 * iconx, 32 * icony, 32, 32, 0, 0, 32, 32);
        const uri = c.toDataURL("image/png");
        savedLogos[coords] = uri;
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