<template>
    <table id="tableComponent" class="table">
        <tbody>
            <tr v-for="item in items" :key="item.title">
                <td>
                    {{ item.title }}
                </td>
                <td>
                    {{ item.size1 }}
                </td>
                <td>
                    {{ item.size2 }}
                </td>
                <td>
                    {{ item.size3 }}
                </td>
                <td>
                    {{ item.size4 }}
                </td>
                <td>
                    {{ item.size5 }}
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>
export default {
    data() {
        return {
            items: [
                {
                    title: 'IT/FR',
                    size1: '44',
                    size2: '46',
                    size3: '48',
                    size4: '50',
                    size5: '52'
                },
                {
                    title: 'UK',
                    size1: '44',
                    size2: '46',
                    size3: '48',
                    size4: '50',
                    size5: '52'
                },
                {
                    title: 'International',
                    size1: '44',
                    size2: '46',
                    size3: '48',
                    size4: '50',
                    size5: '52'
                }
            ]
        };
    }
};
</script>

<style lang="scss">
@import './table.scss';
</style>
