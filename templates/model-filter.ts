export class ModelFilter {

    protected constructor() { }

    private getFields(fields: string[] | string, fieldArgs: string[]) {
        let fieldArray: string[] = [];
        if (typeof(fields) === typeof('')){
            fieldArray = [<string>fields];
        } else {
            fieldArray = <string[]>fields;
        }
        return [...fieldArray, ...fieldArgs];
    }

    public only(fields: string[] | string, ...fieldArgs: string[]): Object {
        const fieldArray = this.getFields(fields, fieldArgs);
        return Object.keys(this)
            .filter(key => fieldArray.includes(key))
            .reduce((obj, key) => {
                obj[key] = this[key];
                return obj;
            }, {});
    }

    public except(fields: string[] | string, ...fieldArgs: string[]): Object {
        const fieldArray = this.getFields(fields, fieldArgs);
        const oppositeFields = Object.keys(this)
            .filter((field) => !fieldArray.includes(field));
        console.log(oppositeFields);
        return this.only(oppositeFields);
    }
}