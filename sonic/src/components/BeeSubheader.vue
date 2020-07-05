<template>
    <span class="beesubheader">
        <span>{{text}}</span>
        <i/>
    </span>
</template>
<script>
    export default {
        props: {
            "text": { type: String, required: true }
        }
    }
</script>