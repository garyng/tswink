export class {{name}} {

  constructor(
  {{# fields }}
    public {{ name }}: {{ type }} = {{ default-value }},
  {{/ fields }}
    ) {
  }

  public only(fields: string[] | string, ...fieldArgs: string[]): Object {
    let fieldArray = [];
    if (typeof(fields) === typeof('')){
      fieldArray = [<string>fields];
    }
    if (fieldArgs.length > 0){
      fieldArray = [...fieldArray, ...fieldArgs];
    }
    return Object.keys(this)
      .filter(key => fieldArray.includes(key))
      .reduce((obj, key) => {
        obj[key] = this[key];
        return obj;
      }, {});
  }

  public except(fields: string[] | string, ...fieldArgs: string[]): Object {
    let fieldArray = [];
    if (typeof(fields) === typeof('')){
      fieldArray = [<string>fields];
    }
    if (fieldArgs.length > 0){
      fieldArray = [...fieldArray, ...fieldArgs];
    }
    const oppositeFields = Object.keys(this)
      .filter((field) => !fieldArray.includes(field));
    return this.only(oppositeFields);
  }

}
