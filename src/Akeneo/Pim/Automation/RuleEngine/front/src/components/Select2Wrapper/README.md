# Select2Wrapper

This component wrap the select2 jquery lib in a react component.

## Use with react hook form

In our use case with react hook form

```javascript
const data = [
  {
    id: 0,
    text: "enhancement",
  },
  {
    id: 1,
    text: "bug",
  },
];

const { register, setValue, getValues } = useForm()
register({ name: 'select2input' })

 <Select2Wrapper
        label="toto" // input should always have a label
        hiddenLabel // that you can't hide (screen readers only)
        onChange={(event) => {
          setValue("select2Input", event.val);
        }}
        id="select2input"
        value={getValues()["select2Input"]}
        data={data}
      />

```

onChange returns a jquery event not a dom event. 
setValue is from react-hook-form allowing you to change the value in the formState.
getValues is from react-hook-form allowing you to get the current formState and access the value

